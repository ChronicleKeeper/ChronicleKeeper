<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Calendar\Domain\Service;

use ChronicleKeeper\Calendar\Domain\Entity\CalendarDate;
use ChronicleKeeper\Calendar\Domain\Service\DateFormatter;
use ChronicleKeeper\Test\Calendar\Domain\Entity\Fixture\ExampleCalendars;
use Generator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(DateFormatter::class)]
#[Small]
class DateFormatterTest extends TestCase
{
    private DateFormatter $formatter;

    protected function setUp(): void
    {
        $this->formatter = new DateFormatter();
    }

    #[Test]
    #[DataProvider('provideDateFormattingCases')]
    public function itFormatsRegularDatesCorrectly(CalendarDate $date, string $format, string $expected): void
    {
        self::assertSame($expected, $this->formatter->format($date, $format));
    }

    public static function provideDateFormattingCases(): Generator
    {
        $calendar = ExampleCalendars::getOnlyRegularDays();
        $date     = new CalendarDate($calendar, 1, 3, 10);

        yield 'Format with day number only' => [
            $date,
            '%d',
            '10',
        ];

        yield 'Format with month name only' => [
            $date,
            '%M',
            'March',
        ];

        yield 'Format with year only' => [
            $date,
            '%y',
            '1',
        ];

        yield 'Format with full date' => [
            $date,
            '%d. %M %Y',
            '10. March 1 AD',
        ];

        yield 'Format with month number' => [
            $date,
            '%m',
            '3',
        ];
    }

    #[Test]
    public function itFormatsLeapDaysCorrectly(): void
    {
        $calendar = ExampleCalendars::getLinearWithLeapDays();
        $date     = new CalendarDate($calendar, 1, 1, 3);

        self::assertSame(
            'Mithwinter 1 after the Flood',
            $this->formatter->format($date, '%d %Y'),
        );
    }
}
