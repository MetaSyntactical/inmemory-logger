<?php

namespace spec\MetaSyntactical\Log\InMemoryLogger;

use InvalidArgumentException;
use MetaSyntactical\Log\InMemoryLogger\LogQuery;
use PhpSpec\ObjectBehavior;
use Psr\Log\LogLevel;

class InMemoryLoggerSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('MetaSyntactical\Log\InMemoryLogger\InMemoryLogger');
    }

    function it_implements_logger_interface()
    {
        $this->shouldHaveType('Psr\Log\LoggerInterface');
    }

    function it_implements_inspectable_interface()
    {
        $this->shouldHaveType('MetaSyntactical\Log\InMemoryLogger\InspectableLogger');
    }

    function it_should_allow_only_valid_log_levels()
    {
        $this
            ->shouldThrow(InvalidArgumentException::class)
            ->duringLog('DUMMY', 'DUMMY');

        $this
            ->shouldNotThrow(InvalidArgumentException::class)
            ->duringLog(LogLevel::ALERT, 'DUMMY');
        $this
            ->shouldNotThrow(InvalidArgumentException::class)
            ->duringLog(LogLevel::CRITICAL, 'DUMMY');
        $this
            ->shouldNotThrow(InvalidArgumentException::class)
            ->duringLog(LogLevel::DEBUG, 'DUMMY');
        $this
            ->shouldNotThrow(InvalidArgumentException::class)
            ->duringLog(LogLevel::EMERGENCY, 'DUMMY');
        $this
            ->shouldNotThrow(InvalidArgumentException::class)
            ->duringLog(LogLevel::ERROR, 'DUMMY');
        $this
            ->shouldNotThrow(InvalidArgumentException::class)
            ->duringLog(LogLevel::INFO, 'DUMMY');
        $this
            ->shouldNotThrow(InvalidArgumentException::class)
            ->duringLog(LogLevel::NOTICE, 'DUMMY');
        $this
            ->shouldNotThrow(InvalidArgumentException::class)
            ->duringLog(LogLevel::WARNING, 'DUMMY');
    }

    function it_should_log_all_available_information()
    {
        $this->alert('Test Message', ['called_class' => 'foo']);

        $this
            ->readLoggedRecords()
            ->shouldHaveCount(1);

        $this
            ->toText()
            ->shouldMatch('(\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}\+\d{2}:\d{2} \[.*?\] .* \{.*?\})');
    }

    function it_should_wipe_logged_entries_correctly()
    {
        $this->alert('Test Message');
        $this->wipeLoggedRecords();

        $this
            ->readLoggedRecords()
            ->shouldHaveCount(0);
    }

    function it_should_log_multiple_entries()
    {
        $this->alert('Test Message');
        $this->alert('Another Test Message');

        $loggedRecords = $this->readLoggedRecords();
        $loggedRecords->shouldHaveCount(2);
    }

    function it_should_bring_all_results_with_empty_search()
    {
        $this->alert('Test Message');
        $this->info('Another Message');

        $logQuery = new LogQuery();

        $this
            ->findLoggedRecord($logQuery)
            ->shouldHaveCount(2);
    }

    function it_should_not_bring_results_during_search_for_not_existing_entry_regexp()
    {
        $this->alert('Test Message');
        $this->alert('Foo bar message');

        $logQuery = new LogQuery();
        $logQuery = $logQuery->withLogMessageRegExp('(aaaaa)');

        $this
            ->findLoggedRecord($logQuery)
            ->shouldHaveCount(0);
    }

    function it_should_not_bring_results_during_search_for_not_existing_entry_string()
    {
        $this->alert('Test Message');
        $this->alert('Foo bar message');

        $logQuery = new LogQuery();
        $logQuery = $logQuery->withLogMessageInString('aaaaa');

        $this
            ->findLoggedRecord($logQuery)
            ->shouldHaveCount(0);
    }

    function it_should_bring_all_matching_results_during_search_for_multiple_matching_entries()
    {
        $this->alert('A Mismatch');
        $this->alert('Test Message');
        $this->alert('Another Test Message');
        $this->alert('Another Mismatch');

        $logQuery = new LogQuery();
        $logQuery = $logQuery->withLogMessageRegExp('(Message)');

        $this
            ->findLoggedRecord($logQuery)
            ->shouldHaveCount(2);
    }

}
