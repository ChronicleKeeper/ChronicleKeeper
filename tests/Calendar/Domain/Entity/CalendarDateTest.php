<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Calendar\Domain\Entity;

use ChronicleKeeper\Calendar\Domain\Entity\CalendarDate;
use ChronicleKeeper\Calendar\Domain\Exception\DayNotExistsInMonth;
use ChronicleKeeper\Calendar\Domain\Exception\MonthNotExists;
use ChronicleKeeper\Test\Calendar\Domain\Entity\Fixture\FullExampleCalendar;
use Generator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(CalendarDate::class)]
#[CoversClass(DayNotExistsInMonth::class)]
#[CoversClass(MonthNotExists::class)]
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
            '4. FirstMonth 2',
        ];
    }

    #[Test]
    public function itFailsCreatingDateWithInvalidDay(): void
    {
        $this->expectException(DayNotExistsInMonth::class);

        $calendar = FullExampleCalendar::get();
        new CalendarDate($calendar, 1, 1, 32);
    }

    #[Test]
    public function itFailsWithCreatingANonExistentMonth(): void
    {
        $this->expectException(MonthNotExists::class);

        $calendar = FullExampleCalendar::get();
        new CalendarDate($calendar, 1, 4, 1);
    }

    #[Test]
    public function itCanAddDaysToDateWithLeapDay(): void
    {
        $calendar = FullExampleCalendar::get();
        $date     = new CalendarDate($calendar, 1, 3, 10);

        $result = $date->addDays(2);
        self::assertSame('EndOfYearSecondLeapDay 1', $result->format());
    }

    #[Test]
    public function isChecksToNotBeingALeapDay(): void
    {
        $calendar = FullExampleCalendar::get();
        $date     = new CalendarDate($calendar, 1, 3, 10);

        self::assertFalse($date->isLeapDay());
    }

    #[Test]
    public function isChecksToBeingALeapDay(): void
    {
        $calendar = FullExampleCalendar::get();
        $date     = new CalendarDate($calendar, 1, 3, 11);

        self::assertTrue($date->isLeapDay());
    }

    #[Test]
    public function ifFormatsARegularDate(): void
    {
        $calendar = FullExampleCalendar::get();
        $date     = new CalendarDate($calendar, 1, 3, 10);

        self::assertSame('10. ThirdMonth 1', $date->format());
    }

    #[Test]
    public function ifFormatsALeapDay(): void
    {
        $calendar = FullExampleCalendar::get();
        $date     = new CalendarDate($calendar, 1, 3, 11);

        self::assertSame('EndOfYearFirstLeapDay 1', $date->format());
    }
}
