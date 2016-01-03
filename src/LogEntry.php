<?php

namespace MetaSyntactical\Log\InMemoryLogger;

use Assert\Assertion;
use DateTimeInterface;
use Psr\Log\LogLevel;
use ReflectionClass;
use RegexGuard\Factory as RegexGuardFactory;

class LogEntry
{
    private $logDate;

    private $logLevel;

    private $logMessage;

    private $logContext;

    private $logCallGraph;

    public function __construct(
        DateTimeInterface $logDate,
        /* string */ $logLevel,
        /* string */ $logMessage,
        array $logContext,
        array $logCallGraph
    ) {
        Assertion::inArray(
            $logLevel,
            (new ReflectionClass(LogLevel::class))->getConstants()
        );
        Assertion::string($logMessage);

        $this->logDate = $logDate;
        $this->logLevel = $logLevel;
        $this->logMessage = $logMessage;
        $this->logContext = $logContext;
        $this->logCallGraph = $logCallGraph;
    }

    public function isBefore(DateTimeInterface $referenceDate)
    {
        return !!$referenceDate->diff($this->logDate)->invert;
    }

    public function isAfter(DateTimeInterface $referenceDate)
    {
        return !$referenceDate->diff($this->logDate)->invert;
    }

    public function isOfLogLevel(array $logLevelList)
    {
        return in_array($this->logLevel, $logLevelList);
    }

    public function containsRegExp($regExp)
    {
        Assertion::string($regExp);
        Assertion::minLength($regExp, 1);

        $guard = RegexGuardFactory::getGuard();

        return !!$guard->match($regExp, $this->logMessage);
    }

    public function containsText($partial)
    {
        Assertion::string($partial);
        Assertion::minLength($partial, 1);

        return mb_strpos($this->logMessage, $partial) !== false;
    }

    public function containsFuzzyContext($partial)
    {
        Assertion::string($partial);
        Assertion::minLength($partial, 1);

        return mb_strpos(json_encode($this->logContext), $partial) !== false;
    }

    public function __toString()
    {
        return sprintf(
            '%s [%s] %s %s',
            $this->logDate->format('c'),
            $this->logLevel,
            $this->logMessage,
            json_encode($this->logContext)
        );
    }
}
