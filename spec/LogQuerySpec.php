<?php

namespace spec\MetaSyntactical\Log\InMemoryLogger;

use DateInterval;
use DateTime;
use DateTimeImmutable;
use DateTimeInterface;
use MetaSyntactical\Log\InMemoryLogger\LogEntry;
use PhpSpec\ObjectBehavior;
use Psr\Log\LogLevel;
use ReflectionObject;
use Webmozart\Assert;

class LogQuerySpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('MetaSyntactical\Log\InMemoryLogger\LogQuery');
    }

    function it_should_not_modify_time_if_datetime_given()
    {
        $dateTime = new DateTime('2000-01-01 00:00:00 UTC');

        $object1 = $this->withLogTimeLowerBounds($dateTime);

        $dateTime->add(new DateInterval('P1Y'));

        $object1->shouldNotPropertyEqualValue('logTimeLowerBounds', $dateTime->format('c'));

        $object1 = $this->withLogTimeUpperBounds($dateTime);

        $dateTime->add(new DateInterval('P1Y'));

        $object1->shouldNotPropertyEqualValue('logTimeUpperBounds', $dateTime->format('c'));
    }

    function it_should_not_modify_time_if_constructed_with_datetime()
    {
        $dateTimeLower = new DateTime('2000-01-01 00:00:00 UTC');
        $dateTimeUpper = new DateTime('2001-01-01 00:00:00 UTC');

        $this->beConstructedWith(
            $dateTimeLower,
            $dateTimeUpper
        );

        $dateTimeLower->add(new DateInterval('P2Y'));
        $dateTimeUpper->add(new DateInterval('P1Y'));

        $this->shouldNotPropertyEqualValue('logTimeLowerBounds', $dateTimeLower->format('c'));
        $this->shouldNotPropertyEqualValue('logTimeUpperBounds', $dateTimeUpper->format('c'));
    }

    function it_is_immutable_to_setting_and_resetting_log_time_lower_bounds()
    {
        $object1 = $this->withLogTimeLowerBounds(new DateTimeImmutable('2000-01-01 00:00:00 UTC'));
        $object2 = $object1->withLogTimeLowerBounds(new DateTimeImmutable('2001-01-01 00:00:00 UTC'));

        $object1->shouldNotPropertyEqual('logTimeLowerBounds', $object2);

        $object3 = $object1->withoutLogTimeLowerBounds();

        $object1->shouldNotPropertyEqual('logTimeLowerBounds', $object3);
    }

    function it_is_immutable_to_setting_and_resetting_log_time_upper_bounds()
    {
        $object1 = $this->withLogTimeUpperBounds(new DateTimeImmutable('2000-01-01 00:00:00 UTC'));
        $object2 = $object1->withLogTimeUpperBounds(new DateTimeImmutable('2001-01-01 00:00:00 UTC'));

        $object1->shouldNotPropertyEqual('logTimeUpperBounds', $object2);

        $object3 = $object1->withoutLogTimeUpperBounds();

        $object1->shouldNotPropertyEqual('logTimeUpperBounds', $object3);
    }

    function it_is_immutable_to_setting_and_resetting_log_level()
    {
        $object1 = $this->withLogLevelList([LogLevel::ALERT]);
        $object2 = $object1->withLogLevelList([LogLevel::CRITICAL, LogLevel::EMERGENCY]);

        $object1->shouldNotPropertyEqual('logLevelList', $object2);

        $object3 = $object1->withoutLogLevelList();

        $object1->shouldNotPropertyEqual('logLevelList', $object3);
    }

    function it_is_immutable_to_setting_and_resetting_log_message_regexp()
    {
        $object1 = $this->withLogMessageRegExp('(message)');
        $object2 = $object1->withLogMessageRegExp('(other)');

        $object1->shouldNotPropertyEqual('logMessageRegExp', $object2);

        $object3 = $object1->withoutLogMessageRegExp();

        $object1->shouldNotPropertyEqual('logMessageRegExp', $object3);
    }

    function it_is_immutable_to_setting_and_resetting_log_message_instring()
    {
        $object1 = $this->withLogMessageInString('message');
        $object2 = $object1->withLogMessageInString('other');

        $object1->shouldNotPropertyEqual('logMessageInString', $object2);

        $object3 = $object1->withoutLogMessageInString();

        $object1->shouldNotPropertyEqual('logMessageInString', $object3);
    }

    function it_is_immutable_to_setting_and_resetting_log_context_fuzzy()
    {
        $object1 = $this->withLogContextFuzzy('foo');
        $object2 = $object1->withLogContextFuzzy('bar');

        $object1->shouldNotPropertyEqual('logContextFuzzy', $object2);

        $object3 = $object1->withoutLogContextFuzzy();

        $object1->shouldNotPropertyEqual('logContextFuzzy', $object3);
    }

    function it_throws_exception_on_invalid_regexp()
    {
        $this
            ->shouldThrow(Assert\InvalidArgumentException::class)
            ->duringWithLogMessageRegExp('((message)');
    }

    function it_throws_exception_on_invalid_bounds()
    {
        $object = $this->withLogTimeLowerBounds(
            new DateTimeImmutable('2005-05-05 08:00:00 UTC')
        );
        $object
            ->shouldThrow(Assert\InvalidArgumentException::class)
            ->duringWithLogTimeUpperBounds(
                new DateTimeImmutable('2005-05-04 08:00:00 UTC')
            );
    }

    function it_should_match_log_time_lower_bounds()
    {
        $this->beConstructedWith(
            new DateTimeImmutable('2005-05-05 08:00:00 UTC')
        );

        $logEntry = new LogEntry(
            new DateTimeImmutable('2005-05-06 08:00:00 UTC'),
            LogLevel::ALERT,
            'Test Message',
            [],
            []
        );

        $this
            ->accepts($logEntry)
            ->shouldBe(true);
    }

    function it_should_not_match_log_time_lower_bounds()
    {
        $this->beConstructedWith(
            new DateTimeImmutable('2005-05-05 08:00:00 UTC')
        );

        $logEntry = new LogEntry(
            new DateTimeImmutable('2005-05-04 08:00:00 UTC'),
            LogLevel::ALERT,
            'Test Message',
            [],
            []
        );

        $this
            ->accepts($logEntry)
            ->shouldBe(false);
    }

    function it_should_match_log_time_upper_bounds()
    {
        $this->beConstructedWith(
            null,
            new DateTimeImmutable('2005-05-05 08:00:00 UTC')
        );

        $logEntry = new LogEntry(
            new DateTimeImmutable('2005-05-04 08:00:00 UTC'),
            LogLevel::ALERT,
            'Test Message',
            [],
            []
        );

        $this
            ->accepts($logEntry)
            ->shouldBe(true);
    }

    function it_should_not_match_log_time_upper_bounds()
    {
        $this->beConstructedWith(
            null,
            new DateTimeImmutable('2005-05-05 08:00:00 UTC')
        );

        $logEntry = new LogEntry(
            new DateTimeImmutable('2005-05-06 08:00:00 UTC'),
            LogLevel::ALERT,
            'Test Message',
            [],
            []
        );

        $this
            ->accepts($logEntry)
            ->shouldBe(false);
    }

    function it_should_match_regexp_message()
    {
        $this->beConstructedWith(
            null,
            null,
            null,
            '(Message)'
        );

        $logEntry = new LogEntry(
            new DateTimeImmutable('2005-05-06 08:00:00 UTC'),
            LogLevel::ALERT,
            'Test Message',
            [],
            []
        );

        $this
            ->accepts($logEntry)
            ->shouldBe(true);
    }

    function it_should_not_match_regexp_message()
    {
        $this->beConstructedWith(
            null,
            null,
            null,
            '(asdf)'
        );

        $logEntry = new LogEntry(
            new DateTimeImmutable('2005-05-06 08:00:00 UTC'),
            LogLevel::ALERT,
            'Test Message',
            [],
            []
        );

        $this
            ->accepts($logEntry)
            ->shouldBe(false);
    }

    function it_should_match_string_message()
    {
        $this->beConstructedWith(
            null,
            null,
            null,
            null,
            'Message'
        );

        $logEntry = new LogEntry(
            new DateTimeImmutable('2005-05-06 08:00:00 UTC'),
            LogLevel::ALERT,
            'Test Message',
            [],
            []
        );

        $this
            ->accepts($logEntry)
            ->shouldBe(true);
    }

    function it_should_not_match_string_message()
    {
        $this->beConstructedWith(
            null,
            null,
            null,
            null,
            'asdf'
        );

        $logEntry = new LogEntry(
            new DateTimeImmutable('2005-05-06 08:00:00 UTC'),
            LogLevel::ALERT,
            'Test Message',
            [],
            []
        );

        $this
            ->accepts($logEntry)
            ->shouldBe(false);
    }

    function it_should_match_fuzzy_context()
    {
        $this->beConstructedWith(
            null,
            null,
            null,
            null,
            null,
            'bananas'
        );

        $logEntry = new LogEntry(
            new DateTimeImmutable('2005-05-06 08:00:00 UTC'),
            LogLevel::ALERT,
            'Test Message',
            [
                'called_class' => 'bananas'
            ],
            []
        );

        $this
            ->accepts($logEntry)
            ->shouldBe(true);
    }

    function it_should_not_match_fuzzy_context()
    {
        $this->beConstructedWith(
            null,
            null,
            null,
            null,
            null,
            'bananas'
        );

        $logEntry = new LogEntry(
            new DateTimeImmutable('2005-05-06 08:00:00 UTC'),
            LogLevel::ALERT,
            'Test Message',
            [
                'called_class' => 'apples'
            ],
            []
        );

        $this
            ->accepts($logEntry)
            ->shouldBe(false);
    }

    function it_should_match_log_level()
    {
        $this->beConstructedWith(
            null,
            null,
            [LogLevel::ALERT, LogLevel::CRITICAL]
        );

        $logEntry = new LogEntry(
            new DateTimeImmutable('2005-05-06 08:00:00 UTC'),
            LogLevel::ALERT,
            'Test Message',
            [],
            []
        );

        $this
            ->accepts($logEntry)
            ->shouldBe(true);
    }

    function it_should_not_match_log_level()
    {
        $this->beConstructedWith(
            null,
            null,
            [LogLevel::ALERT, LogLevel::CRITICAL]
        );

        $logEntry = new LogEntry(
            new DateTimeImmutable('2005-05-06 08:00:00 UTC'),
            LogLevel::INFO,
            'Test Message',
            [],
            []
        );

        $this
            ->accepts($logEntry)
            ->shouldBe(false);
    }

    public function getMatchers(): array
    {
        return [
            'propertyEqual' => function ($subject, $propertyKey, $comparedObject) {
                $subjectProperty = (new ReflectionObject($subject))->getProperty($propertyKey);
                $subjectProperty->setAccessible(true);

                return $subjectProperty->getValue($subject) == $subjectProperty->getValue($comparedObject);
            },
            'propertyEqualValue' => function ($subject, $propertyKey, $comparedValue) {
                $subjectProperty = (new ReflectionObject($subject))->getProperty($propertyKey);
                $subjectProperty->setAccessible(true);

                $subjectValue = $subjectProperty->getValue($subject);
                if ($subjectValue instanceof DateTimeInterface) {
                    $subjectValue = $subjectValue->format('c');
                }

                return $subjectValue === $comparedValue;
            },
        ];
    }
}
