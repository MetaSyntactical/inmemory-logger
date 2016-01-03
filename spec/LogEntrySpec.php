<?php

namespace spec\MetaSyntactical\Log\InMemoryLogger;

use DateTimeImmutable;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Psr\Log\LogLevel;

class LogEntrySpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->beConstructedWith(
            new DateTimeImmutable(),
            LogLevel::INFO,
            'Test message',
            [],
            []
        );

        $this->shouldHaveType('MetaSyntactical\Log\InMemoryLogger\LogEntry');
    }

    function it_should_throw_exception_on_invalid_parameters()
    {
        $this->beConstructedWith(
            new DateTimeImmutable(),
            'Invalid LogLevel',
            'Test Message',
            [],
            []
        );

        $this
            ->shouldThrow('Assert\InvalidArgumentException')
            ->duringInstantiation();
    }

    function it_should_be_before_date()
    {
        $this->beConstructedWith(
            new DateTimeImmutable('2010-02-03 08:00:00'),
            LogLevel::INFO,
            'Test message',
            [],
            []
        );

        $this
            ->isBefore(new DateTimeImmutable('2010-02-05 08:00:00'))
            ->shouldBe(true);
    }

    function it_should_not_be_before_date()
    {
        $this->beConstructedWith(
            new DateTimeImmutable('2010-02-03 08:00:00'),
            LogLevel::INFO,
            'Test message',
            [],
            []
        );

        $this
            ->isBefore(new DateTimeImmutable('2010-02-01 08:00:00'))
            ->shouldBe(false);
    }

    function it_should_be_after_date()
    {
        $this->beConstructedWith(
            new DateTimeImmutable('2010-02-03 08:00:00'),
            LogLevel::INFO,
            'Test message',
            [],
            []
        );

        $this
            ->isAfter(new DateTimeImmutable('2010-02-01 08:00:00'))
            ->shouldBe(true);
    }

    function it_should_not_be_after_date()
    {
        $this->beConstructedWith(
            new DateTimeImmutable('2010-02-03 08:00:00'),
            LogLevel::INFO,
            'Test message',
            [],
            []
        );

        $this
            ->isAfter(new DateTimeImmutable('2010-02-05 08:00:00'))
            ->shouldBe(false);
    }

    function it_should_throw_exception_on_invalid_regexp()
    {
        $this->beConstructedWith(
            new DateTimeImmutable('2010-02-03 08:00:00'),
            LogLevel::INFO,
            'Test message',
            [],
            []
        );

        $this
            ->shouldThrow('RegexGuard\RegexException')
            ->duringContainsRegExp('((message)');
    }

    function it_should_contain_regexp_text_in_message()
    {
        $this->beConstructedWith(
            new DateTimeImmutable('2010-02-03 08:00:00'),
            LogLevel::INFO,
            'Test message',
            [],
            []
        );

        $this
            ->containsRegExp('(message)')
            ->shouldBe(true);
    }

    function it_should_not_contain_regexp_text_in_message()
    {
        $this->beConstructedWith(
            new DateTimeImmutable('2010-02-03 08:00:00'),
            LogLevel::INFO,
            'Test message',
            [],
            []
        );

        $this
            ->containsRegExp('(notfound)')
            ->shouldBe(false);
    }

    function it_should_contain_string_in_message()
    {
        $this->beConstructedWith(
            new DateTimeImmutable('2010-02-03 08:00:00'),
            LogLevel::INFO,
            'Test message',
            [],
            []
        );

        $this
            ->containsText('st mes')
            ->shouldBe(true);
    }

    function it_should_not_contain_string_in_message()
    {
        $this->beConstructedWith(
            new DateTimeImmutable('2010-02-03 08:00:00'),
            LogLevel::INFO,
            'Test message',
            [],
            []
        );

        $this
            ->containsText('asdf')
            ->shouldBe(false);
    }

    function it_should_contain_fuzzy_context()
    {
        $this->beConstructedWith(
            new DateTimeImmutable('2010-02-03 08:00:00'),
            LogLevel::INFO,
            'Test message',
            ['called_class' => 'fuzzyclass'],
            []
        );

        $this
            ->containsFuzzyContext('fuzzy')
            ->shouldBe(true);
    }

    function it_should_not_contain_fuzzy_context()
    {
        $this->beConstructedWith(
            new DateTimeImmutable('2010-02-03 08:00:00'),
            LogLevel::INFO,
            'Test message',
            ['called_class' => 'fuzzyclass'],
            []
        );

        $this
            ->containsFuzzyContext('foo')
            ->shouldBe(false);
    }
}
