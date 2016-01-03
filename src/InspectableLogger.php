<?php

namespace MetaSyntactical\Log\InMemoryLogger;

interface InspectableLogger
{
    /**
     * Retrieve list of log entries in chronological order.
     *
     * @return LogEntry[]
     */
    public function readLoggedRecords();

    /**
     * Search for a log entry in the given limits.
     *
     * If a field in the LogQuery equals null the specific attribute
     * is considered omitted and will be ignored.
     *
     * @param LogQuery $logQuery
     * @return LogEntry[]
     */
    public function findLoggedRecord(LogQuery $logQuery);

    /**
     * Convert stored log entries to structured list.
     *
     * @return string
     */
    public function toText();

    /**
     * Clear list of stored log entries.
     */
    public function wipeLoggedRecords();
}
