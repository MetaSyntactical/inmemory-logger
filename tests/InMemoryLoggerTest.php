<?php

declare(strict_types=1);

use MetaSyntactical\Log\InMemoryLogger\InMemoryLogger;
use MetaSyntactical\Log\InMemoryLogger\InspectableLogger;
use MetaSyntactical\Log\InMemoryLogger\LogQuery;
use PHPUnit\Framework\TestCase;
use Psr\Log\LogLevel;
use Psr\Log\LoggerInterface;

final class InMemoryLoggerTest extends TestCase
{
    public function testItIsInitializable(): void
    {
        $logger = new InMemoryLogger();
        $this->assertInstanceOf(InMemoryLogger::class, $logger);
    }

    public function testItImplementsLoggerInterface(): void
    {
        $logger = new InMemoryLogger();
        $this->assertInstanceOf(LoggerInterface::class, $logger);
    }

    public function testItImplementsInspectableInterface(): void
    {
        $logger = new InMemoryLogger();
        $this->assertInstanceOf(InspectableLogger::class, $logger);
    }

    public function testItShouldThrowOnInvalidLogLevel(): void
    {
        $logger = new InMemoryLogger();
        $this->expectException(InvalidArgumentException::class);
        $logger->log('DUMMY', 'DUMMY');
    }

    public function testItShouldAllowAllValidLogLevels(): void
    {
        $logger = new InMemoryLogger();

        $logger->log(LogLevel::ALERT, 'DUMMY');
        $logger->log(LogLevel::CRITICAL, 'DUMMY');
        $logger->log(LogLevel::DEBUG, 'DUMMY');
        $logger->log(LogLevel::EMERGENCY, 'DUMMY');
        $logger->log(LogLevel::ERROR, 'DUMMY');
        $logger->log(LogLevel::INFO, 'DUMMY');
        $logger->log(LogLevel::NOTICE, 'DUMMY');
        $logger->log(LogLevel::WARNING, 'DUMMY');

        $this->assertTrue(true, 'All valid log levels should not throw');
    }

    public function testItShouldLogAllAvailableInformation(): void
    {
        $logger = new InMemoryLogger();
        $logger->alert('Test Message', ['called_class' => 'foo']);

        $records = $logger->readLoggedRecords();
        $this->assertCount(1, $records);

        $text = $logger->toText();
        $this->assertMatchesRegularExpression(
            '/\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}\+\d{2}:\d{2} \[.*?\] .* \{.*?\}/',
            $text
        );
    }

    public function testItShouldWipeLoggedEntriesCorrectly(): void
    {
        $logger = new InMemoryLogger();
        $logger->alert('Test Message');
        $logger->wipeLoggedRecords();

        $records = $logger->readLoggedRecords();
        $this->assertCount(0, $records);
    }

    public function testItShouldLogMultipleEntries(): void
    {
        $logger = new InMemoryLogger();
        $logger->alert('Test Message');
        $logger->alert('Another Test Message');

        $records = $logger->readLoggedRecords();
        $this->assertCount(2, $records);
    }

    public function testItShouldBringAllResultsWithEmptySearch(): void
    {
        $logger = new InMemoryLogger();
        $logger->alert('Test Message');
        $logger->info('Another Message');

        $logQuery = new LogQuery();

        $results = $logger->findLoggedRecord($logQuery);
        $this->assertCount(2, $results);
    }

    public function testItShouldNotBringResultsDuringSearchForNotExistingEntryRegexp(): void
    {
        $logger = new InMemoryLogger();
        $logger->alert('Test Message');
        $logger->alert('Foo bar message');

        $logQuery = (new LogQuery())->withLogMessageRegExp('(aaaaa)');

        $results = $logger->findLoggedRecord($logQuery);
        $this->assertCount(0, $results);
    }

    public function testItShouldNotBringResultsDuringSearchForNotExistingEntryString(): void
    {
        $logger = new InMemoryLogger();
        $logger->alert('Test Message');
        $logger->alert('Foo bar message');

        $logQuery = (new LogQuery())->withLogMessageInString('aaaaa');

        $results = $logger->findLoggedRecord($logQuery);
        $this->assertCount(0, $results);
    }

    public function testItShouldBringAllMatchingResultsDuringSearchForMultipleMatchingEntries(): void
    {
        $logger = new InMemoryLogger();
        $logger->alert('A Mismatch');
        $logger->alert('Test Message');
        $logger->alert('Another Test Message');
        $logger->alert('Another Mismatch');

        $logQuery = (new LogQuery())->withLogMessageRegExp('(Message)');

        $results = $logger->findLoggedRecord($logQuery);
        $this->assertCount(2, $results);
    }
}
