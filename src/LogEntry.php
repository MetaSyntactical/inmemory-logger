<?php

namespace MetaSyntactical\Log\InMemoryLogger;

use DateTimeInterface;
use Psr\Log\LogLevel;
use ReflectionClass;
use RegexGuard\Factory as RegexGuardFactory;
use Webmozart\Assert\Assert;

final class LogEntry
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
        Assert::inArray(
            $logLevel,
            (new ReflectionClass(LogLevel::class))->getConstants()
        );
        Assert::string($logMessage);

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
        Assert::string($regExp);
        Assert::minLength($regExp, 1);

        $guard = RegexGuardFactory::getGuard();

        return !!$guard->match($regExp, $this->logMessage);
    }

    public function containsText($partial)
    {
        Assert::string($partial);
        Assert::minLength($partial, 1);

        return mb_strpos($this->logMessage, $partial) !== false;
    }

    public function containsFuzzyContext($partial)
    {
        Assert::string($partial);
        Assert::minLength($partial, 1);

        return mb_strpos(json_encode($this->logContext), $partial) !== false;
    }

    public function __toString()
    {
        return sprintf(
            '%s [%s] %s %s',
            $this->logDate->format('c'),
            $this->logLevel,
            $this->replaceCurlyBrackets($this->logMessage, $this->logContext),
            json_encode($this->logContext)
        );
    }

    private function replaceCurlyBrackets($logMessage, $logContext)
    {
        return preg_replace_callback(
            '(\{(.*?)})',
            function ($matches) use ($logContext) {
                if (isset($logContext[$matches[1]])) {
                    if (is_object($logContext[$matches[1]])
                        && !method_exists($logContext[$matches[1]], '__toString')) {
                        return get_class($logContext[$matches[1]]);
                    }

                    return (string) $logContext[$matches[1]];
                }

                return $matches[0];
            },
            $logMessage
        );
    }
}
