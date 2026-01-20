<?php

declare(strict_types=1);

use MetaSyntactical\Log\InMemoryLogger\LogEntry;
use PHPUnit\Framework\TestCase;
use Psr\Log\LogLevel;
use RegexGuard\RegexException;
use Webmozart\Assert\InvalidArgumentException;

final class LogEntryTest extends TestCase
{
    public function testItIsInitializable(): void
    {
        $entry = new LogEntry(
            new DateTimeImmutable(),
            LogLevel::INFO,
            'Test message',
            [],
            []
        );

        $this->assertInstanceOf(LogEntry::class, $entry);
    }

    public function testItShouldThrowExceptionOnInvalidParameters(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new LogEntry(
            new DateTimeImmutable(),
            'Invalid LogLevel',
            'Test Message',
            [],
            []
        );
    }

    public function testItShouldBeBeforeDate(): void
    {
        $entry = new LogEntry(
            new DateTimeImmutable('2010-02-03 08:00:00'),
            LogLevel::INFO,
            'Test message',
            [],
            []
        );

        $this->assertTrue($entry->isBefore(new DateTimeImmutable('2010-02-05 08:00:00')));
    }

    public function testItShouldNotBeBeforeDate(): void
    {
        $entry = new LogEntry(
            new DateTimeImmutable('2010-02-03 08:00:00'),
            LogLevel::INFO,
            'Test message',
            [],
            []
        );

        $this->assertFalse($entry->isBefore(new DateTimeImmutable('2010-02-01 08:00:00')));
    }

    public function testItShouldBeAfterDate(): void
    {
        $entry = new LogEntry(
            new DateTimeImmutable('2010-02-03 08:00:00'),
            LogLevel::INFO,
            'Test message',
            [],
            []
        );

        $this->assertTrue($entry->isAfter(new DateTimeImmutable('2010-02-01 08:00:00')));
    }

    public function testItShouldNotBeAfterDate(): void
    {
        $entry = new LogEntry(
            new DateTimeImmutable('2010-02-03 08:00:00'),
            LogLevel::INFO,
            'Test message',
            [],
            []
        );

        $this->assertFalse($entry->isAfter(new DateTimeImmutable('2010-02-05 08:00:00')));
    }

    public function testItShouldThrowExceptionOnInvalidRegexp(): void
    {
        $entry = new LogEntry(
            new DateTimeImmutable('2010-02-03 08:00:00'),
            LogLevel::INFO,
            'Test message',
            [],
            []
        );

        $this->expectException(RegexException::class);
        $entry->containsRegExp('((message)');
    }

    public function testItShouldContainRegexpTextInMessage(): void
    {
        $entry = new LogEntry(
            new DateTimeImmutable('2010-02-03 08:00:00'),
            LogLevel::INFO,
            'Test message',
            [],
            []
        );

        $this->assertTrue($entry->containsRegExp('(message)'));
    }

    public function testItShouldNotContainRegexpTextInMessage(): void
    {
        $entry = new LogEntry(
            new DateTimeImmutable('2010-02-03 08:00:00'),
            LogLevel::INFO,
            'Test message',
            [],
            []
        );

        $this->assertFalse($entry->containsRegExp('(notfound)'));
    }

    public function testItShouldContainStringInMessage(): void
    {
        $entry = new LogEntry(
            new DateTimeImmutable('2010-02-03 08:00:00'),
            LogLevel::INFO,
            'Test message',
            [],
            []
        );

        $this->assertTrue($entry->containsText('st mes'));
    }

    public function testItShouldNotContainStringInMessage(): void
    {
        $entry = new LogEntry(
            new DateTimeImmutable('2010-02-03 08:00:00'),
            LogLevel::INFO,
            'Test message',
            [],
            []
        );

        $this->assertFalse($entry->containsText('asdf'));
    }

    public function testItShouldContainFuzzyContext(): void
    {
        $entry = new LogEntry(
            new DateTimeImmutable('2010-02-03 08:00:00'),
            LogLevel::INFO,
            'Test message',
            ['called_class' => 'fuzzyclass'],
            []
        );

        $this->assertTrue($entry->containsFuzzyContext('fuzzy'));
    }

    public function testItShouldNotContainFuzzyContext(): void
    {
        $entry = new LogEntry(
            new DateTimeImmutable('2010-02-03 08:00:00'),
            LogLevel::INFO,
            'Test message',
            ['called_class' => 'fuzzyclass'],
            []
        );

        $this->assertFalse($entry->containsFuzzyContext('foo'));
    }

    public function testItShouldReplaceCurlyBracketsWithContentsFromContext(): void
    {
        $inner = new LogEntry(
            new DateTimeImmutable('2010-02-02 00:00:00 UTC'),
            LogLevel::INFO,
            'Test',
            [],
            []
        );

        $entry = new LogEntry(
            new DateTimeImmutable('2010-02-03 08:00:00 UTC'),
            LogLevel::INFO,
            'Test {replace} message {object} {world}',
            [
                'replace' => 'with',
                'object' => new stdClass(),
                'world' => $inner,
            ],
            []
        );

        $expected = '2010-02-03T08:00:00+00:00 [info] Test with message stdClass 2010-02-02T00:00:00+00:00 [info] Test [] {"replace":"with","object":{},"world":{}}';
        $this->assertSame($expected, (string)$entry);
    }
}
