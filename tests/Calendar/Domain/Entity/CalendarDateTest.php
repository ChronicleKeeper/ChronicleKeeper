<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Calendar\Domain\Entity;

use ChronicleKeeper\Calendar\Domain\Entity\CalendarDate;
use ChronicleKeeper\Test\Calendar\Domain\Entity\Fixture\FullExampleCalendar;
use Generator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(CalendarDate::class)]
#[Small]
class CalendarDateTest extends TestCase
{
    #[Test]
    #[DataProvider('provideDayCalculationCases')]
    public function itCanAddDaysToDate(CalendarDate $sourceDate, int $daysToAdd, string $expectedResult): void
    {
        $result = $sourceDate->addDays($daysToAdd);
        self::assertSame($expectedResult, $result->format());
    }

    public static function provideDayCalculationCases(): Generator
    {
        $calendar = FullExampleCalendar::get();

        yield 'Add 4 day to the beginning of the year' => [
            new CalendarDate($calendar, 1, 1, 1),
            4,
            '5. FirstMonth 1',
        ];

        yield 'Add 15 days to the beginning of the year' => [
            new CalendarDate($calendar, 1, 1, 1),
            15,
            '6. SecondMonth 1',
        ];

        yield 'Add 40 days to the beginning of the year, year changes' => [
            new CalendarDate($calendar, 1, 1, 1),
            40,
            '6. FirstMonth 2',
        ];
    }
}