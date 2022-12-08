<?php

namespace MetaSyntactical\Log\InMemoryLogger;

use DateTimeImmutable;
use InvalidArgumentException;
use Psr\Log\LoggerInterface;
use Psr\Log\LoggerTrait;
use Psr\Log\LogLevel;
use ReflectionClass;
use Stringable;

final class InMemoryLogger implements LoggerInterface, InspectableLogger
{
    use LoggerTrait;

    private $records;

    private $allowedLogLevels;

    public function __construct()
    {
        $this->allowedLogLevels = $this->loadAllowedLogLevels();
        $this->wipeLoggedRecords();
    }

    /**
     * {@inheritdoc}
     */
    public function log($level, string|Stringable $message, array $context = []): void
    {
        if (!in_array($level, $this->allowedLogLevels)) {
            throw new InvalidArgumentException(
                sprintf(
                    'Invalid level "%s", please use one of %s',
                    $level,
                    json_encode(array_values($this->allowedLogLevels))
                )
            );
        }

        $this->records[] = new LogEntry(
            new DateTimeImmutable(),
            $level,
            $message,
            $context,
            []
        );
    }

    /**
     * {@inheritdoc}
     */
    public function readLoggedRecords()
    {
        return array_map(
            function ($object) {
                return clone $object;
            },
            $this->records
        );
    }

    /**
     * {@inheritdoc}
     */
    public function toText()
    {
        $result = '';
        foreach ($this->records as $record) {
            $result .= (string) $record . "\n";
        }

        return trim($result, "\n");
    }

    /**
     * {@inheritdoc}
     */
    public function findLoggedRecord(LogQuery $logQuery)
    {
        return array_filter(
            $this->readLoggedRecords(),
            [$logQuery, 'accepts']
        );
    }

    /**
     * {@inheritdoc}
     */
    public function wipeLoggedRecords()
    {
        $this->records = [];
    }

    private function loadAllowedLogLevels()
    {
        return (new ReflectionClass(LogLevel::class))->getConstants();
    }
}
