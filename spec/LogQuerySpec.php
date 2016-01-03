<?php

namespace spec\MetaSyntactical\Log\InMemoryLogger;

use DateTimeImmutable;
use MetaSyntactical\Log\InMemoryLogger\LogEntry;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Psr\Log\LogLevel;

class LogQuerySpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('MetaSyntactical\Log\InMemoryLogger\LogQuery');
    }

    function it_throws_exception_on_invalid_regexp()
    {
        $this
            ->shouldThrow('Assert\InvalidArgumentException')
            ->duringWithLogMessageRegExp('((message)');
    }

    function it_throws_exception_on_invalid_bounds()
    {
        $object = $this->withLogTimeLowerBounds(
            new DateTimeImmutable('2005-05-05 08:00:00 UTC')
        );
        $object
            ->shouldThrow('Assert\InvalidArgumentException')
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
}
