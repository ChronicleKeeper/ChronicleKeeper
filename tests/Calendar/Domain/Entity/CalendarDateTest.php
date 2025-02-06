<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Calendar\Domain\Entity;

use ChronicleKeeper\Calendar\Domain\Entity\CalendarDate;
use ChronicleKeeper\Calendar\Domain\Exception\DayNotExistsInMonth;
use ChronicleKeeper\Calendar\Domain\Exception\MonthNotExists;
use ChronicleKeeper\Calendar\Domain\Exception\YearIsInvalidInCalendar;
use ChronicleKeeper\Calendar\Domain\ValueObject\MoonState;
use ChronicleKeeper\Test\Calendar\Domain\Entity\Fixture\ExampleCalendars;
use Generator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(CalendarDate::class)]
#[CoversClass(DayNotExistsInMonth::class)]
#[CoversClass(MonthNotExists::class)]
#[CoversClass(YearIsInvalidInCalendar::class)]
#[Small]
class CalendarDateTest extends TestCase
{
    #[Test]
    public function itIsAbleToCreateADateInRegularCalendar(): void
    {
        $calendar = ExampleCalendars::getOnlyRegularDays();
        $date     = new CalendarDate($calendar, 1, 3, 10);

        self::assertSame(1, $date->getYear());
        self::assertSame(3, $date->getMonth());
        self::assertSame('10', $date->getDay());
    }

    #[Test]
    #[DataProvider('provideDayCalculationCases')]
    public function itCanAddDaysToDate(CalendarDate $sourceDate, int $daysToAdd, string $expectedResult): void
    {
        $result = $sourceDate->addDays($daysToAdd);
        self::assertSame($expectedResult, $result->format());
    }

    public static function provideDayCalculationCases(): Generator
    {
        $calendar = ExampleCalendars::getFullFeatured();

        yield 'Add 4 day to the beginning of the year' => [
            new CalendarDate($calendar, 1, 1, 1),
            4,
            '5. FirstMonth 1 after Boom',
        ];

        yield 'Add 15 days to the beginning of the year' => [
            new CalendarDate($calendar, 1, 1, 1),
            15,
            '6. SecondMonth 1 after Boom',
        ];

        yield 'Add 40 days to the beginning of the year, year changes' => [
            new CalendarDate($calendar, 1, 1, 1),
            40,
            '6. FirstMonth 2 after Boom',
        ];
    }

    #[Test]
    public function itFailsCreatingDateWithInvalidDay(): void
    {
        $this->expectException(DayNotExistsInMonth::class);

        $calendar = ExampleCalendars::getFullFeatured();
        new CalendarDate($calendar, 1, 1, 32);
    }

    #[Test]
    public function itFailsWithCreatingANonExistentMonth(): void
    {
        $this->expectException(MonthNotExists::class);

        $calendar = ExampleCalendars::getFullFeatured();
        new CalendarDate($calendar, 1, 4, 1);
    }

    #[Test]
    public function ifFormatsARegularDate(): void
    {
        $calendar = ExampleCalendars::getFullFeatured();
        $date     = new CalendarDate($calendar, 1, 3, 10);

        self::assertSame('10. ThirdMonth 1 after Boom', $date->format());
    }

    #[Test]
    #[DataProvider('provideRegularWeekDayCalculationCases')]
    public function itCalculatesWeekDaysCorrectWithRegularDayCalendar(CalendarDate $date, int $expectedWeekDay): void
    {
        self::assertSame($expectedWeekDay, $date->getWeekDay()->index);
    }

    public static function provideRegularWeekDayCalculationCases(): Generator
    {
        $calendar = ExampleCalendars::getOnlyRegularDays();

        // Year 0
        yield 'First day of the first month of year 0' => [new CalendarDate($calendar, 0, 1, 1), 1];
        yield 'Last day of the first month of year 0' => [new CalendarDate($calendar, 0, 1, 31), 3];
        yield 'First day of the second month of year 0' => [new CalendarDate($calendar, 0, 2, 1), 4];
        yield 'Last day of the second month of year 0' => [new CalendarDate($calendar, 0, 2, 28), 3];
        yield 'First day of the third month of year 0' => [new CalendarDate($calendar, 0, 3, 1), 4];
        yield 'Last day of the third month of year 0' => [new CalendarDate($calendar, 0, 3, 31), 6];
        yield 'First day of the fourth month of year 0' => [new CalendarDate($calendar, 0, 4, 1), 7];
        yield 'Last day of the fourth month of year 0' => [new CalendarDate($calendar, 0, 4, 30), 1];
        yield 'First day of the fifth month of year 0' => [new CalendarDate($calendar, 0, 5, 1), 2];
        yield 'Last day of the fifth month of year 0' => [new CalendarDate($calendar, 0, 5, 31), 4];
        yield 'First day of the sixth month of year 0' => [new CalendarDate($calendar, 0, 6, 1), 5];
        yield 'Last day of the sixth month of year 0' => [new CalendarDate($calendar, 0, 6, 30), 6];
        yield 'First day of the seventh month of year 0' => [new CalendarDate($calendar, 0, 7, 1), 7];
        yield 'Last day of the seventh month of year 0' => [new CalendarDate($calendar, 0, 7, 31), 2];
        yield 'First day of the eighth month of year 0' => [new CalendarDate($calendar, 0, 8, 1), 3];
        yield 'Last day of the eighth month of year 0' => [new CalendarDate($calendar, 0, 8, 31), 5];
        yield 'First day of the ninth month of year 0' => [new CalendarDate($calendar, 0, 9, 1), 6];
        yield 'Last day of the ninth month of year 0' => [new CalendarDate($calendar, 0, 9, 30), 7];
        yield 'First day of the tenth month of year 0' => [new CalendarDate($calendar, 0, 10, 1), 1];
        yield 'Last day of the tenth month of year 0' => [new CalendarDate($calendar, 0, 10, 31), 3];
        yield 'First day of the eleventh month of year 0' => [new CalendarDate($calendar, 0, 11, 1), 4];
        yield 'Last day of the eleventh month of year 0' => [new CalendarDate($calendar, 0, 11, 30), 5];
        yield 'First day of the twelfth month of year 0' => [new CalendarDate($calendar, 0, 12, 1), 6];
        yield 'Last day of the twelfth month of year 0' => [new CalendarDate($calendar, 0, 12, 31), 1];

        // Year 1
        yield 'First day of the first month of year 1' => [new CalendarDate($calendar, 1, 1, 1), 2];
        yield 'Last day of the first month of year 1' => [new CalendarDate($calendar, 1, 1, 31), 4];
        yield 'First day of the second month of year 1' => [new CalendarDate($calendar, 1, 2, 1), 5];
        yield 'Last day of the second month of year 1' => [new CalendarDate($calendar, 1, 2, 28), 4];
        yield 'First day of the third month of year 1' => [new CalendarDate($calendar, 1, 3, 1), 5];
        yield 'Last day of the third month of year 1' => [new CalendarDate($calendar, 1, 3, 31), 7];
        yield 'First day of the fourth month of year 1' => [new CalendarDate($calendar, 1, 4, 1), 1];
        yield 'Last day of the fourth month of year 1' => [new CalendarDate($calendar, 1, 4, 30), 2];
        yield 'First day of the fifth month of year 1' => [new CalendarDate($calendar, 1, 5, 1), 3];
        yield 'Last day of the fifth month of year 1' => [new CalendarDate($calendar, 1, 5, 31), 5];
        yield 'First day of the sixth month of year 1' => [new CalendarDate($calendar, 1, 6, 1), 6];
        yield 'Last day of the sixth month of year 1' => [new CalendarDate($calendar, 1, 6, 30), 7];
        yield 'First day of the seventh month of year 1' => [new CalendarDate($calendar, 1, 7, 1), 1];
        yield 'Last day of the seventh month of year 1' => [new CalendarDate($calendar, 1, 7, 31), 3];
        yield 'First day of the eighth month of year 1' => [new CalendarDate($calendar, 1, 8, 1), 4];
        yield 'Last day of the eighth month of year 1' => [new CalendarDate($calendar, 1, 8, 31), 6];
        yield 'First day of the ninth month of year 1' => [new CalendarDate($calendar, 1, 9, 1), 7];
        yield 'Last day of the ninth month of year 1' => [new CalendarDate($calendar, 1, 9, 30), 1];
        yield 'First day of the tenth month of year 1' => [new CalendarDate($calendar, 1, 10, 1), 2];
        yield 'Last day of the tenth month of year 1' => [new CalendarDate($calendar, 1, 10, 31), 4];
        yield 'First day of the eleventh month of year 1' => [new CalendarDate($calendar, 1, 11, 1), 5];
        yield 'Last day of the eleventh month of year 1' => [new CalendarDate($calendar, 1, 11, 30), 6];
        yield 'First day of the twelfth month of year 1' => [new CalendarDate($calendar, 1, 12, 1), 7];
        yield 'Last day of the twelfth month of year 1' => [new CalendarDate($calendar, 1, 12, 31), 2];
    }

    #[Test]
    #[DataProvider('provideLastDayCases')]
    public function itIsGivingTheCorrectLastDayOfAMonth(CalendarDate $date, string $expectedDay): void
    {
        self::assertSame($expectedDay, $date->getLastDayOfMonth()->format());
    }

    public static function provideLastDayCases(): Generator
    {
        $calendar = ExampleCalendars::getOnlyRegularDays();

        yield 'Last day of january 0 AD in regular day calendar' => [
            new CalendarDate($calendar, 0, 1, 12),
            '31. January 0 AD',
        ];

        yield 'Last day of february 0 AD in regular day calendar' => [
            new CalendarDate($calendar, 0, 2, 12),
            '28. February 0 AD',
        ];
    }

    #[Test]
    #[DataProvider('provideFirstDayCases')]
    public function itIsGivingTheCorrectFirstDayOfAMonth(CalendarDate $date, string $expectedDay): void
    {
        self::assertSame($expectedDay, $date->getFirstDayOfMonth()->format());
    }

    public static function provideFirstDayCases(): Generator
    {
        $calendar = ExampleCalendars::getOnlyRegularDays();

        yield 'Last day of january 0 AD in regular day calendar' => [
            new CalendarDate($calendar, 0, 1, 12),
            '1. January 0 AD',
        ];

        yield 'Last day of february 0 AD in regular day calendar' => [
            new CalendarDate($calendar, 0, 2, 12),
            '1. February 0 AD',
        ];
    }

    #[Test]
    #[DataProvider('provideDiffInDaysCases')]
    public function itCalculatesTheDifferenceInDaysBetweenTwoDates(
        CalendarDate $date,
        CalendarDate $otherDate,
        int $expectedDiff,
    ): void {
        self::assertSame($expectedDiff, $date->diffInDays($otherDate));
    }

    public static function provideDiffInDaysCases(): Generator
    {
        $calendar = ExampleCalendars::getOnlyRegularDays();

        yield 'Difference between two dates in the same month in regular calendar' => [
            new CalendarDate($calendar, 0, 1, 1),
            new CalendarDate($calendar, 0, 1, 5),
            4,
        ];

        yield 'Difference between two dates in different months in regular calendar' => [
            new CalendarDate($calendar, 0, 1, 1),
            new CalendarDate($calendar, 0, 2, 5),
            35,
        ];

        yield 'Difference between two dates in different years in regular calendar' => [
            new CalendarDate($calendar, 0, 1, 1),
            new CalendarDate($calendar, 1, 1, 1),
            365,
        ];
    }

    #[Test]
    #[DataProvider('provideMoonStateCasesWithNonLinearCalendar')]
    public function itCanCalculateTheCurrentMoonState(CalendarDate $date, MoonState $expectedMoonState): void
    {
        self::assertSame($expectedMoonState, $date->getMoonState());
    }

    public static function provideMoonStateCasesWithNonLinearCalendar(): Generator
    {
        $calendar = ExampleCalendars::getOnlyRegularDays();

        yield 'Day 1: New Moon in regular calendar' => [
            new CalendarDate($calendar, 0, 1, 1),
            MoonState::NEW_MOON,
        ];

        yield 'Day 5: Waxing Crescent in regular calendar' => [
            new CalendarDate($calendar, 0, 1, 5),
            MoonState::WAXING_CRESCENT,
        ];

        yield 'Day 7: First Quarter in regular calendar' => [
            new CalendarDate($calendar, 0, 1, 7),
            MoonState::FIRST_QUARTER,
        ];

        yield 'Day 8: First Quarter in regular calendar' => [
            new CalendarDate($calendar, 0, 1, 8),
            MoonState::FIRST_QUARTER,
        ];

        yield 'Day 11: Waxing Gibbous in regular calendar' => [
            new CalendarDate($calendar, 0, 1, 11),
            MoonState::WAXING_GIBBOUS,
        ];

        yield 'Day 15: Full Moon in regular calendar' => [
            new CalendarDate($calendar, 0, 1, 15),
            MoonState::FULL_MOON,
        ];

        yield 'Day 18: Waning Gibbous in regular calendar' => [
            new CalendarDate($calendar, 0, 1, 18),
            MoonState::WANING_GIBBOUS,
        ];

        yield 'Day 22: Last Quarter in regular calendar' => [
            new CalendarDate($calendar, 0, 1, 22),
            MoonState::LAST_QUARTER,
        ];

        yield 'Day 26: Waning Crescent in regular calendar' => [
            new CalendarDate($calendar, 0, 1, 26),
            MoonState::WANING_CRESCENT,
        ];

        yield 'Day 29: New Moon in regular calendar' => [
            new CalendarDate($calendar, 0, 1, 29),
            MoonState::NEW_MOON,
        ];
    }

    #[Test]
    #[DataProvider('provideTotalDaysCountCases')]
    public function itCalculatesTheTotalDaysFromTheStartOfTheCalendar(CalendarDate $date, int $expectedDays): void
    {
        self::assertSame($expectedDays, $date->getTotalDaysFromCalendarStart());
    }

    public static function provideTotalDaysCountCases(): Generator
    {
        $calendar = ExampleCalendars::getOnlyRegularDays();

        yield 'First day of the first month of year 0 in regular calendar' => [
            new CalendarDate($calendar, 0, 1, 1),
            1,
        ];

        yield 'Last day of the first month of year 0 in regular calendar' => [
            new CalendarDate($calendar, 0, 1, 31),
            31,
        ];

        yield 'First day of the second month of year 0 in regular calendar' => [
            new CalendarDate($calendar, 0, 2, 1),
            32,
        ];

        yield 'Last day of the second month of year 0 in regular calendar' => [
            new CalendarDate($calendar, 0, 2, 28),
            59,
        ];

        yield 'First day of the third month of year 0 in regular calendar' => [
            new CalendarDate($calendar, 0, 3, 1),
            60,
        ];

        yield 'Last day of the third month of year 0 in regular calendar' => [
            new CalendarDate($calendar, 0, 3, 31),
            90,
        ];
    }

    #[Test]
    #[DataProvider('provideSubDaysCases')]
    public function itCanSubtractDaysFromDate(
        CalendarDate $sourceDate,
        int $daysToSubtract,
        string $expectedResult,
    ): void {
        $result = $sourceDate->subDays($daysToSubtract);
        self::assertSame($expectedResult, $result->format());
    }

    public static function provideSubDaysCases(): Generator
    {
        $calendar = ExampleCalendars::getOnlyRegularDays();

        yield 'Subtract 4 days from the beginning of the year in regular calendar' => [
            new CalendarDate($calendar, 1, 1, 1),
            4,
            '28. December 0 AD',
        ];

        yield 'Subtract 15 days from the beginning of the year in regular calendar' => [
            new CalendarDate($calendar, 1, 1, 1),
            15,
            '17. December 0 AD',
        ];

        yield 'Subtract 40 days from the beginning of the year, year changes in regular calendar' => [
            new CalendarDate($calendar, 1, 1, 1),
            40,
            '22. November 0 AD',
        ];

        yield 'Subtract 365 days from the beginning of the year, year changes in regular calendar' => [
            new CalendarDate($calendar, 1, 1, 1),
            365,
            '1. January 0 AD',
        ];
    }

    #[Test]
    public function itIsNotAbleToSubDaysBeforeCalendarStart(): void
    {
        $this->expectException(YearIsInvalidInCalendar::class);

        $calendar = ExampleCalendars::getOnlyRegularDays();
        $date     = new CalendarDate($calendar, 0, 1, 1);
        $date->subDays(1);
    }

    #[Test]
    public function itIsAbleToCompareToSameDate(): void
    {
        $calendar    = ExampleCalendars::getOnlyRegularDays();
        $date        = new CalendarDate($calendar, 0, 1, 1);
        $compareDate = new CalendarDate($calendar, 0, 1, 1);

        self::assertNotSame($date, $compareDate);
        self::assertTrue($date->isSame($compareDate));
    }

    #[Test]
    public function itIsAbleToCompareToDifferentDate(): void
    {
        $calendar    = ExampleCalendars::getOnlyRegularDays();
        $date        = new CalendarDate($calendar, 0, 1, 1);
        $compareDate = new CalendarDate($calendar, 0, 1, 2);

        self::assertNotSame($date, $compareDate);
        self::assertFalse($date->isSame($compareDate));
    }

    #[Test]
    #[DataProvider('provideFirstWeekDayCases')]
    public function itCanGetTheCorrectFirstDayOfTheWeek(
        CalendarDate $date,
        string $expectedDate,
    ): void {
        $firstWeekDay = $date->getFirstDayOfWeek();

        self::assertSame(1, $firstWeekDay->getWeekDay()->index);
        self::assertSame($expectedDate, $firstWeekDay->format());
    }

    public static function provideFirstWeekDayCases(): Generator
    {
        $calendar = ExampleCalendars::getOnlyRegularDays();

        yield 'First day of the regular calendar is also the first weekday' => [
            new CalendarDate($calendar, 0, 1, 1),
            '1. January 0 AD',
        ];

        yield 'Some day in the first week of the regular calendar will return to first day' => [
            new CalendarDate($calendar, 0, 1, 2),
            '1. January 0 AD',
        ];

        yield 'Some random day in the regular calendar year 0 has correct weekday' => [
            new CalendarDate($calendar, 0, 7, 15),
            '9. July 0 AD',
        ];

        yield 'A random day in the regular calendar year 15 has correct weekday' => [
            new CalendarDate($calendar, 12, 7, 15),
            '11. July 12 AD',
        ];
    }
}
