<?php

declare(strict_types=1);

use MetaSyntactical\Log\InMemoryLogger\LogEntry;
use MetaSyntactical\Log\InMemoryLogger\LogQuery;
use PHPUnit\Framework\TestCase;
use Psr\Log\LogLevel;
use Webmozart\Assert\InvalidArgumentException;

final class LogQueryTest extends TestCase
{
    public function testItIsInitializable(): void
    {
        $query = new LogQuery();
        $this->assertInstanceOf(LogQuery::class, $query);
    }

    public function testItShouldNotModifyTimeIfDateTimeGiven(): void
    {
        $dateTime = new DateTime('2000-01-01 00:00:00 UTC');

        $object1 = (new LogQuery())->withLogTimeLowerBounds($dateTime);

        $dateTime->add(new DateInterval('P1Y'));

        $this->assertNotSame(
            $dateTime->format('c'),
            $this->formatIfDateTime($this->getPrivateProperty($object1, 'logTimeLowerBounds'))
        );

        $object1 = (new LogQuery())->withLogTimeUpperBounds($dateTime);

        $dateTime->add(new DateInterval('P1Y'));

        $this->assertNotSame(
            $dateTime->format('c'),
            $this->formatIfDateTime($this->getPrivateProperty($object1, 'logTimeUpperBounds'))
        );
    }

    public function testItShouldNotModifyTimeIfConstructedWithDateTime(): void
    {
        $dateTimeLower = new DateTime('2000-01-01 00:00:00 UTC');
        $dateTimeUpper = new DateTime('2001-01-01 00:00:00 UTC');

        $query = new LogQuery(
            $dateTimeLower,
            $dateTimeUpper
        );

        $dateTimeLower->add(new DateInterval('P2Y'));
        $dateTimeUpper->add(new DateInterval('P1Y'));

        $this->assertNotSame(
            $dateTimeLower->format('c'),
            $this->formatIfDateTime($this->getPrivateProperty($query, 'logTimeLowerBounds'))
        );
        $this->assertNotSame(
            $dateTimeUpper->format('c'),
            $this->formatIfDateTime($this->getPrivateProperty($query, 'logTimeUpperBounds'))
        );
    }

    public function testItIsImmutableToSettingAndResettingLogTimeLowerBounds(): void
    {
        $object1 = (new LogQuery())->withLogTimeLowerBounds(new DateTimeImmutable('2000-01-01 00:00:00 UTC'));
        $object2 = $object1->withLogTimeLowerBounds(new DateTimeImmutable('2001-01-01 00:00:00 UTC'));

        $v1 = $this->formatIfDateTime($this->getPrivateProperty($object1, 'logTimeLowerBounds'));
        $v2 = $this->formatIfDateTime($this->getPrivateProperty($object2, 'logTimeLowerBounds'));
        $this->assertNotEquals($v1, $v2);

        $object3 = $object1->withoutLogTimeLowerBounds();

        $v3 = $this->formatIfDateTime($this->getPrivateProperty($object3, 'logTimeLowerBounds'));
        $this->assertNotEquals($v1, $v3);
    }

    public function testItIsImmutableToSettingAndResettingLogTimeUpperBounds(): void
    {
        $object1 = (new LogQuery())->withLogTimeUpperBounds(new DateTimeImmutable('2000-01-01 00:00:00 UTC'));
        $object2 = $object1->withLogTimeUpperBounds(new DateTimeImmutable('2001-01-01 00:00:00 UTC'));

        $v1 = $this->formatIfDateTime($this->getPrivateProperty($object1, 'logTimeUpperBounds'));
        $v2 = $this->formatIfDateTime($this->getPrivateProperty($object2, 'logTimeUpperBounds'));
        $this->assertNotEquals($v1, $v2);

        $object3 = $object1->withoutLogTimeUpperBounds();

        $v3 = $this->formatIfDateTime($this->getPrivateProperty($object3, 'logTimeUpperBounds'));
        $this->assertNotEquals($v1, $v3);
    }

    public function testItIsImmutableToSettingAndResettingLogLevel(): void
    {
        $object1 = (new LogQuery())->withLogLevelList([LogLevel::ALERT]);
        $object2 = $object1->withLogLevelList([LogLevel::CRITICAL, LogLevel::EMERGENCY]);

        $v1 = $this->getPrivateProperty($object1, 'logLevelList');
        $v2 = $this->getPrivateProperty($object2, 'logLevelList');
        $this->assertNotEquals($v1, $v2);

        $object3 = $object1->withoutLogLevelList();

        $v3 = $this->getPrivateProperty($object3, 'logLevelList');
        $this->assertNotEquals($v1, $v3);
    }

    public function testItIsImmutableToSettingAndResettingLogMessageRegexp(): void
    {
        $object1 = (new LogQuery())->withLogMessageRegExp('(message)');
        $object2 = $object1->withLogMessageRegExp('(other)');

        $v1 = $this->getPrivateProperty($object1, 'logMessageRegExp');
        $v2 = $this->getPrivateProperty($object2, 'logMessageRegExp');
        $this->assertNotEquals($v1, $v2);

        $object3 = $object1->withoutLogMessageRegExp();

        $v3 = $this->getPrivateProperty($object3, 'logMessageRegExp');
        $this->assertNotEquals($v1, $v3);
    }

    public function testItIsImmutableToSettingAndResettingLogMessageInString(): void
    {
        $object1 = (new LogQuery())->withLogMessageInString('message');
        $object2 = $object1->withLogMessageInString('other');

        $v1 = $this->getPrivateProperty($object1, 'logMessageInString');
        $v2 = $this->getPrivateProperty($object2, 'logMessageInString');
        $this->assertNotEquals($v1, $v2);

        $object3 = $object1->withoutLogMessageInString();

        $v3 = $this->getPrivateProperty($object3, 'logMessageInString');
        $this->assertNotEquals($v1, $v3);
    }

    public function testItIsImmutableToSettingAndResettingLogContextFuzzy(): void
    {
        $object1 = (new LogQuery())->withLogContextFuzzy('foo');
        $object2 = $object1->withLogContextFuzzy('bar');

        $v1 = $this->getPrivateProperty($object1, 'logContextFuzzy');
        $v2 = $this->getPrivateProperty($object2, 'logContextFuzzy');
        $this->assertNotEquals($v1, $v2);

        $object3 = $object1->withoutLogContextFuzzy();

        $v3 = $this->getPrivateProperty($object3, 'logContextFuzzy');
        $this->assertNotEquals($v1, $v3);
    }

    public function testItThrowsExceptionOnInvalidRegexp(): void
    {
        $this->expectException(InvalidArgumentException::class);
        (new LogQuery())->withLogMessageRegExp('((message)');
    }

    public function testItThrowsExceptionOnInvalidBounds(): void
    {
        $object = (new LogQuery())->withLogTimeLowerBounds(
            new DateTimeImmutable('2005-05-05 08:00:00 UTC')
        );

        $this->expectException(InvalidArgumentException::class);
        $object->withLogTimeUpperBounds(
            new DateTimeImmutable('2005-05-04 08:00:00 UTC')
        );
    }

    public function testItShouldMatchLogTimeLowerBounds(): void
    {
        $query = new LogQuery(
            new DateTimeImmutable('2005-05-05 08:00:00 UTC')
        );

        $logEntry = new LogEntry(
            new DateTimeImmutable('2005-05-06 08:00:00 UTC'),
            LogLevel::ALERT,
            'Test Message',
            [],
            []
        );

        $this->assertTrue($query->accepts($logEntry));
    }

    public function testItShouldNotMatchLogTimeLowerBounds(): void
    {
        $query = new LogQuery(
            new DateTimeImmutable('2005-05-05 08:00:00 UTC')
        );

        $logEntry = new LogEntry(
            new DateTimeImmutable('2005-05-04 08:00:00 UTC'),
            LogLevel::ALERT,
            'Test Message',
            [],
            []
        );

        $this->assertFalse($query->accepts($logEntry));
    }

    public function testItShouldMatchLogTimeUpperBounds(): void
    {
        $query = new LogQuery(
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

        $this->assertTrue($query->accepts($logEntry));
    }

    public function testItShouldNotMatchLogTimeUpperBounds(): void
    {
        $query = new LogQuery(
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

        $this->assertFalse($query->accepts($logEntry));
    }

    public function testItShouldMatchRegexpMessage(): void
    {
        $query = new LogQuery(
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

        $this->assertTrue($query->accepts($logEntry));
    }

    public function testItShouldNotMatchRegexpMessage(): void
    {
        $query = new LogQuery(
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

        $this->assertFalse($query->accepts($logEntry));
    }

    public function testItShouldMatchStringMessage(): void
    {
        $query = new LogQuery(
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

        $this->assertTrue($query->accepts($logEntry));
    }

    public function testItShouldNotMatchStringMessage(): void
    {
        $query = new LogQuery(
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

        $this->assertFalse($query->accepts($logEntry));
    }

    public function testItShouldMatchFuzzyContext(): void
    {
        $query = new LogQuery(
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
                'called_class' => 'bananas',
            ],
            []
        );

        $this->assertTrue($query->accepts($logEntry));
    }

    public function testItShouldNotMatchFuzzyContext(): void
    {
        $query = new LogQuery(
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
                'called_class' => 'apples',
            ],
            []
        );

        $this->assertFalse($query->accepts($logEntry));
    }

    public function testItShouldMatchLogLevel(): void
    {
        $query = new LogQuery(
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

        $this->assertTrue($query->accepts($logEntry));
    }

    public function testItShouldNotMatchLogLevel(): void
    {
        $query = new LogQuery(
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

        $this->assertFalse($query->accepts($logEntry));
    }

    private function getPrivateProperty(object $subject, string $property)
    {
        $ref = new ReflectionObject($subject);
        $prop = $ref->getProperty($property);
        $prop->setAccessible(true);
        return $prop->getValue($subject);
    }

    private function formatIfDateTime($value): mixed
    {
        if ($value instanceof DateTimeInterface) {
            return $value->format('c');
        }
        return $value;
    }
}
