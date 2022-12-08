<?php

namespace MetaSyntactical\Log\InMemoryLogger;

use DateTime;
use DateTimeImmutable;
use DateTimeInterface;
use Psr\Log\LogLevel;
use ReflectionClass;
use RegexGuard\Factory as RegexGuardFactory;
use Webmozart\Assert\Assert;

final class LogQuery
{
    private $logTimeLowerBounds;

    private $logTimeUpperBounds;

    private $logLevelList;

    private $logMessageRegExp;

    private $logMessageInString;

    private $logContextFuzzy;

    /**
     * Create new LogQuery.
     *
     * All parameters may be optional and specified later ({@see with*}).
     *
     * @param DateTimeInterface|null $logTimeLowerBounds
     * @param DateTimeInterface|null $logTimeUpperBounds
     * @param null $logLevelList
     * @param null $logMessageRegExp
     * @param null $logMessageInString
     * @param null $logContextFuzzy
     */
    public function __construct(
        DateTimeInterface $logTimeLowerBounds = null,
        DateTimeInterface $logTimeUpperBounds = null,
        /* string[] */ $logLevelList = null,
        /* string */ $logMessageRegExp = null,
        /* string */ $logMessageInString = null,
        /* string */ $logContextFuzzy = null
    ) {
        Assert::nullOrIsInstanceOf($logTimeLowerBounds, DateTimeInterface::class);
        Assert::nullOrIsInstanceOf($logTimeUpperBounds, DateTimeInterface::class);
        Assert::nullOrIsArray($logLevelList);
        Assert::nullOrString($logMessageRegExp);
        Assert::nullOrString($logMessageInString);
        Assert::nullOrString($logContextFuzzy);

        if ($logTimeLowerBounds instanceof DateTime) {
            $logTimeLowerBounds = DateTimeImmutable::createFromMutable($logTimeLowerBounds);
        }
        if ($logTimeUpperBounds instanceof DateTime) {
            $logTimeUpperBounds = DateTimeImmutable::createFromMutable($logTimeUpperBounds);
        }

        $this->logTimeLowerBounds = $logTimeLowerBounds;
        $this->logTimeUpperBounds = $logTimeUpperBounds;
        $this->logLevelList = $logLevelList;
        $this->logMessageRegExp = $logMessageRegExp;
        $this->logMessageInString = $logMessageInString;
        $this->logContextFuzzy = $logContextFuzzy;

        $this->validate();
    }

    /**
     * Add LogTimeLowerBounds.
     *
     * @param DateTimeInterface $logTimeLowerBounds
     * @return LogQuery
     */
    public function withLogTimeLowerBounds(DateTimeInterface $logTimeLowerBounds)
    {
        if ($logTimeLowerBounds instanceof DateTime) {
            $logTimeLowerBounds = DateTimeImmutable::createFromMutable($logTimeLowerBounds);
        }

        return $this->immutableWith('logTimeLowerBounds', $logTimeLowerBounds);
    }

    /**
     * Remove LogTimeLowerBounds.
     *
     * @return LogQuery
     */
    public function withoutLogTimeLowerBounds()
    {
        return $this->immutableWith('logTimeLowerBounds');
    }

    /**
     * Add LogTimeUpperBounds.
     *
     * @param DateTimeInterface $logTimeUpperBounds
     * @return LogQuery
     */
    public function withLogTimeUpperBounds(DateTimeInterface $logTimeUpperBounds)
    {
        if ($logTimeUpperBounds instanceof DateTime) {
            $logTimeUpperBounds = DateTimeImmutable::createFromMutable($logTimeUpperBounds);
        }

        return $this->immutableWith('logTimeUpperBounds', $logTimeUpperBounds);
    }

    /**
     * Remove LogTimeUpperBounds.
     *
     * @return LogQuery
     */
    public function withoutLogTimeUpperBounds()
    {
        return $this->immutableWith('logTimeUpperBounds');
    }

    /**
     * Add LogLevelList.
     *
     * @param string[] $logLevelList list of valid log levels (@see Psr\LogLevel}
     * @return LogQuery
     */
    public function withLogLevelList(array $logLevelList)
    {
        return $this->immutableWith('logLevelList', $logLevelList);
    }

    /**
     * Remove LogLevelList.
     *
     * @return LogQuery
     */
    public function withoutLogLevelList()
    {
        return $this->immutableWith('logLevelList');
    }

    /**
     * Add LogMessageRegExp.
     *
     * @param string $logMessageRegExp
     * @return LogQuery
     */
    public function withLogMessageRegExp(/* string */ $logMessageRegExp)
    {
        Assert::string($logMessageRegExp);
        Assert::minLength($logMessageRegExp, 1);

        return $this->immutableWith('logMessageRegExp', $logMessageRegExp);
    }

    /**
     * Remove LogMessageRegExp.
     *
     * @return LogQuery
     */
    public function withoutLogMessageRegExp()
    {
        return $this->immutableWith('logMessageRegExp');
    }

    /**
     * Add LogMessageInString.
     *
     * @param string $logMessageInString
     * @return LogQuery
     */
    public function withLogMessageInString(/* string */ $logMessageInString)
    {
        Assert::string($logMessageInString);
        Assert::minLength($logMessageInString, 1);

        return $this->immutableWith('logMessageInString', $logMessageInString);
    }

    /**
     * Remove LogMessageInString.
     *
     * @return LogQuery
     */
    public function withoutLogMessageInString()
    {
        return $this->immutableWith('logMessageInString');
    }

    /**
     * Add LogContextFuzzy.
     *
     * @param string $logContextFuzzy
     * @return LogQuery
     */
    public function withLogContextFuzzy(/* string */ $logContextFuzzy)
    {
        Assert::string($logContextFuzzy);
        Assert::minLength($logContextFuzzy, 1);

        return $this->immutableWith('logContextFuzzy', $logContextFuzzy);
    }

    /**
     * Remove LogContextFuzzy.
     *
     * @return LogQuery
     */
    public function withoutLogContextFuzzy()
    {
        return $this->immutableWith('logContextFuzzy');
    }

    /**
     * Check whether given LogEntry would be matched by the LogQuery.
     *
     * @param LogEntry $logEntry
     * @return bool
     */
    public function accepts(LogEntry $logEntry)
    {
        if (!is_null($this->logTimeLowerBounds)) {
            if ($logEntry->isBefore($this->logTimeLowerBounds)) {
                return false;
            }
        }

        if (!is_null($this->logTimeUpperBounds)) {
            if ($logEntry->isAfter($this->logTimeUpperBounds)) {
                return false;
            }
        }

        if (!is_null($this->logLevelList)) {
            if (!$logEntry->isOfLogLevel($this->logLevelList)) {
                return false;
            }
        }

        if (!is_null($this->logMessageRegExp)) {
            if (!$logEntry->containsRegExp($this->logMessageRegExp)) {
                return false;
            }
        }

        if (!is_null($this->logMessageInString)) {
            if (!$logEntry->containsText($this->logMessageInString)) {
                return false;
            }
        }

        if (!is_null($this->logContextFuzzy)) {
            if (!$logEntry->containsFuzzyContext($this->logContextFuzzy)) {
                return false;
            }
        }

        return true;
    }

    private function validate()
    {
        Assert::nullOrString($this->logMessageRegExp);
        Assert::nullOrString($this->logMessageInString);
        Assert::nullOrString($this->logContextFuzzy);
        Assert::nullOrIsInstanceOf($this->logTimeLowerBounds, DateTimeInterface::class);
        Assert::nullOrIsInstanceOf($this->logTimeUpperBounds, DateTimeInterface::class);
        Assert::nullOrIsArray($this->logLevelList);

        if (!is_null($this->logLevelList)) {
            Assert::notEmpty($this->logLevelList);
            Assert::allInArray(
                $this->logLevelList,
                array_values((new ReflectionClass(LogLevel::class))->getConstants())
            );
        }

        if (!is_null($this->logMessageRegExp)) {
            Assert::true(
                RegexGuardFactory::getGuard()->isRegexValid($this->logMessageRegExp)
            );
        }

        if (!is_null($this->logTimeLowerBounds) && !is_null($this->logTimeUpperBounds)) {
            Assert::false(
                $this->logTimeLowerBounds->diff($this->logTimeUpperBounds)->invert
            );
        }
    }

    private function immutableWith($propertyName = null, $propertyValue = null)
    {
        $newObject = clone $this;
        if (!is_null($propertyName)) {
            $newObject->$propertyName = $propertyValue;
            $newObject->validate();
        }

        return $newObject;
    }
}
