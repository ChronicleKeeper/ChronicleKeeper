<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Calendar\Domain\Entity;

use ChronicleKeeper\Calendar\Domain\Entity\Calendar;
use ChronicleKeeper\Calendar\Domain\Entity\CalendarDate;
use ChronicleKeeper\Calendar\Domain\Exception\DayNotExistsInMonth;
use ChronicleKeeper\Calendar\Domain\Exception\MonthNotExists;
use ChronicleKeeper\Calendar\Domain\Exception\YearIsInvalidInCalendar;
use ChronicleKeeper\Calendar\Domain\Service\DateFormatter;
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
#[CoversClass(DateFormatter::class)]
#[Small]
class CalendarDateTest extends TestCase
{
    #[Test]
    #[DataProvider('provideDateCreationCases')]
    public function itIsAbleToCreateADateInRegularCalendar(
        Calendar $calendar,
        int $year,
        int $month,
        int $day,
        string $expectedDayLabel,
    ): void {
        $date = new CalendarDate($calendar, $year, $month, $day);

        self::assertSame($year, $date->getYear());
        self::assertSame($month, $date->getMonth());
        self::assertSame($expectedDayLabel, $date->getDay()->getLabel());
    }

    public static function provideDateCreationCases(): Generator
    {
        $calendar = ExampleCalendars::getOnlyRegularDays();

        yield 'Regular calendar date - some day' => [
            $calendar,
            1,
            3,
            10,
            '10',
        ];

        yield 'Regular calendar date - another some day' => [
            $calendar,
            1,
            2,
            28,
            '28',
        ];

        $calendar = ExampleCalendars::getLinearWithLeapDays();

        yield 'Linear calendar date with leap day' => [
            $calendar,
            1,
            2,
            29,
            '29',
        ];

        yield 'Linear calendar date with leap day - create leap day' => [
            $calendar,
            1,
            1,
            3,
            'Mithwinter',
        ];

        yield 'Linear calendar date with leap day - leap day in non interval year' => [
            $calendar,
            2,
            7,
            2,
            '2',
        ];
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
        $calendar = ExampleCalendars::getOnlyRegularDays();

        yield 'Regular Calendar - Add 4 day to the beginning of the year' => [
            new CalendarDate($calendar, 1, 1, 1),
            4,
            '5. January 1 AD',
        ];

        yield 'Regular Calendar - Add 98 days to the beginning of the year' => [
            new CalendarDate($calendar, 1, 1, 1),
            98,
            '9. April 1 AD',
        ];

        yield 'Regular Calendar - Add 370 days to the beginning of the year, year changes' => [
            new CalendarDate($calendar, 1, 1, 1),
            370,
            '6. January 2 AD',
        ];

        $calendar = ExampleCalendars::getLinearWithLeapDays();

        yield 'Linear Calendar - Add 4 day to the beginning of the year' => [
            new CalendarDate($calendar, 1, 1, 1),
            4,
            '4. Taranis 1 after the Flood',
        ];

        yield 'Linear Calendar - Add 2 day to the beginning of the year' => [
            new CalendarDate($calendar, 1, 1, 1),
            2,
            'Mithwinter 1 after the Flood',
        ];

        yield 'Linear Calendar - Add days to a date and finish on interval inactive leap day' => [
            new CalendarDate($calendar, 1, 6, 24),
            8,
            '2. Arthan 1 after the Flood',
        ];

        yield 'Linear Calendar - Add days to a date and finish on interval active leap day' => [
            new CalendarDate($calendar, 4, 6, 24),
            8,
            'Shieldday 4 after the Flood',
        ];

        yield 'Linear Calendar - Add a single day to a interval active leap day' => [
            new CalendarDate($calendar, 4, 7, 2),
            1,
            '2. Arthan 4 after the Flood',
        ];

        yield 'Linear Calendar - Add two days to a interval active leap day' => [
            new CalendarDate($calendar, 4, 7, 2),
            2,
            '3. Arthan 4 after the Flood',
        ];

        yield 'Linear Calendar - Add enough days to land at an inactive leap day' => [
            new CalendarDate($calendar, 3, 7, 30),
            2,
            '1. Telisias 3 after the Flood',
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
    public function itFailsCreatingADateAtALeapDayInactiveIntervalYear(): void
    {
        $this->expectException(DayNotExistsInMonth::class);

        $calendar = ExampleCalendars::getLinearWithLeapDays();
        new CalendarDate($calendar, 2, 7, 32);
    }

    #[Test]
    public function itFailsWithCreatingANonExistentMonth(): void
    {
        $this->expectException(MonthNotExists::class);

        $calendar = ExampleCalendars::getFullFeatured();
        new CalendarDate($calendar, 1, 4, 1);
    }

    #[Test]
    public function itFormatsALeapDay(): void
    {
        $calendar = ExampleCalendars::getLinearWithLeapDays();
        $date     = new CalendarDate($calendar, 1, 1, 3);

        self::assertSame('Mithwinter 1 after the Flood', $date->format());
    }

    #[Test]
    #[DataProvider('provideRegularWeekDayCalculationCases')]
    public function itCalculatesWeekDaysCorrect(CalendarDate $date, int|null $expectedWeekDay): void
    {
        self::assertSame($expectedWeekDay, $date->getWeekDay()?->index);
    }

    public static function provideRegularWeekDayCalculationCases(): Generator
    {
        $calendar = ExampleCalendars::getOnlyRegularDays();

        // Year 0
        yield 'RegularCalendar: First day of the first month of year 0' => [new CalendarDate($calendar, 0, 1, 1), 1];
        yield 'RegularCalendar: Last day of the first month of year 0' => [new CalendarDate($calendar, 0, 1, 31), 3];
        yield 'RegularCalendar: First day of the second month of year 0' => [new CalendarDate($calendar, 0, 2, 1), 4];
        yield 'RegularCalendar: Last day of the second month of year 0' => [new CalendarDate($calendar, 0, 2, 28), 3];
        yield 'RegularCalendar: First day of the third month of year 0' => [new CalendarDate($calendar, 0, 3, 1), 4];
        yield 'RegularCalendar: Last day of the third month of year 0' => [new CalendarDate($calendar, 0, 3, 31), 6];
        yield 'RegularCalendar: First day of the fourth month of year 0' => [new CalendarDate($calendar, 0, 4, 1), 7];
        yield 'RegularCalendar: Last day of the fourth month of year 0' => [new CalendarDate($calendar, 0, 4, 30), 1];
        yield 'RegularCalendar: First day of the fifth month of year 0' => [new CalendarDate($calendar, 0, 5, 1), 2];
        yield 'RegularCalendar: Last day of the fifth month of year 0' => [new CalendarDate($calendar, 0, 5, 31), 4];
        yield 'RegularCalendar: First day of the sixth month of year 0' => [new CalendarDate($calendar, 0, 6, 1), 5];
        yield 'RegularCalendar: Last day of the sixth month of year 0' => [new CalendarDate($calendar, 0, 6, 30), 6];
        yield 'RegularCalendar: First day of the seventh month of year 0' => [new CalendarDate($calendar, 0, 7, 1), 7];
        yield 'RegularCalendar: Last day of the seventh month of year 0' => [new CalendarDate($calendar, 0, 7, 31), 2];
        yield 'RegularCalendar: First day of the eighth month of year 0' => [new CalendarDate($calendar, 0, 8, 1), 3];
        yield 'RegularCalendar: Last day of the eighth month of year 0' => [new CalendarDate($calendar, 0, 8, 31), 5];
        yield 'RegularCalendar: First day of the ninth month of year 0' => [new CalendarDate($calendar, 0, 9, 1), 6];
        yield 'RegularCalendar: Last day of the ninth month of year 0' => [new CalendarDate($calendar, 0, 9, 30), 7];
        yield 'RegularCalendar: First day of the tenth month of year 0' => [new CalendarDate($calendar, 0, 10, 1), 1];
        yield 'RegularCalendar: Last day of the tenth month of year 0' => [new CalendarDate($calendar, 0, 10, 31), 3];
        yield 'RegularCalendar: First day of the eleventh month of year 0' => [new CalendarDate($calendar, 0, 11, 1), 4];
        yield 'RegularCalendar: Last day of the eleventh month of year 0' => [new CalendarDate($calendar, 0, 11, 30), 5];
        yield 'RegularCalendar: First day of the twelfth month of year 0' => [new CalendarDate($calendar, 0, 12, 1), 6];
        yield 'RegularCalendar: Last day of the twelfth month of year 0' => [new CalendarDate($calendar, 0, 12, 31), 1];

        // Year 1
        yield 'RegularCalendar: First day of the first month of year 1' => [new CalendarDate($calendar, 1, 1, 1), 2];
        yield 'RegularCalendar: Last day of the first month of year 1' => [new CalendarDate($calendar, 1, 1, 31), 4];
        yield 'RegularCalendar: First day of the second month of year 1' => [new CalendarDate($calendar, 1, 2, 1), 5];
        yield 'RegularCalendar: Last day of the second month of year 1' => [new CalendarDate($calendar, 1, 2, 28), 4];
        yield 'RegularCalendar: First day of the third month of year 1' => [new CalendarDate($calendar, 1, 3, 1), 5];
        yield 'RegularCalendar: Last day of the third month of year 1' => [new CalendarDate($calendar, 1, 3, 31), 7];
        yield 'RegularCalendar: First day of the fourth month of year 1' => [new CalendarDate($calendar, 1, 4, 1), 1];
        yield 'RegularCalendar: Last day of the fourth month of year 1' => [new CalendarDate($calendar, 1, 4, 30), 2];
        yield 'RegularCalendar: First day of the fifth month of year 1' => [new CalendarDate($calendar, 1, 5, 1), 3];
        yield 'RegularCalendar: Last day of the fifth month of year 1' => [new CalendarDate($calendar, 1, 5, 31), 5];
        yield 'RegularCalendar: First day of the sixth month of year 1' => [new CalendarDate($calendar, 1, 6, 1), 6];
        yield 'RegularCalendar: Last day of the sixth month of year 1' => [new CalendarDate($calendar, 1, 6, 30), 7];
        yield 'RegularCalendar: First day of the seventh month of year 1' => [new CalendarDate($calendar, 1, 7, 1), 1];
        yield 'RegularCalendar: Last day of the seventh month of year 1' => [new CalendarDate($calendar, 1, 7, 31), 3];
        yield 'RegularCalendar: First day of the eighth month of year 1' => [new CalendarDate($calendar, 1, 8, 1), 4];
        yield 'RegularCalendar: Last day of the eighth month of year 1' => [new CalendarDate($calendar, 1, 8, 31), 6];
        yield 'RegularCalendar: First day of the ninth month of year 1' => [new CalendarDate($calendar, 1, 9, 1), 7];
        yield 'RegularCalendar: Last day of the ninth month of year 1' => [new CalendarDate($calendar, 1, 9, 30), 1];
        yield 'RegularCalendar: First day of the tenth month of year 1' => [new CalendarDate($calendar, 1, 10, 1), 2];
        yield 'RegularCalendar: Last day of the tenth month of year 1' => [new CalendarDate($calendar, 1, 10, 31), 4];
        yield 'RegularCalendar: First day of the eleventh month of year 1' => [new CalendarDate($calendar, 1, 11, 1), 5];
        yield 'RegularCalendar: Last day of the eleventh month of year 1' => [new CalendarDate($calendar, 1, 11, 30), 6];
        yield 'RegularCalendar: First day of the twelfth month of year 1' => [new CalendarDate($calendar, 1, 12, 1), 7];
        yield 'RegularCalendar: Last day of the twelfth month of year 1' => [new CalendarDate($calendar, 1, 12, 31), 2];

        $calendar = ExampleCalendars::getLinearWithLeapDays();

        // Year 0
        yield 'LinearCalendar LeapDays: First day of the first month of year 0' => [new CalendarDate($calendar, 0, 1, 1), 1];
        yield 'LinearCalendar LeapDays: Third day of the first month of year 0' => [new CalendarDate($calendar, 0, 1, 3), null];
        yield 'LinearCalendar LeapDays: Last day of the first month of year 0' => [new CalendarDate($calendar, 0, 1, 31), 10];

        // Year 1
        yield 'LinearCalendar LeapDays: First day of the seventh month of year 0' => [new CalendarDate($calendar, 0, 7, 1), 1];
        yield 'LinearCalendar LeapDays: Second day of the seventh month of year 0' => [new CalendarDate($calendar, 0, 7, 2), null];
        yield 'LinearCalendar LeapDays: Last day of the seventh month of year 0' => [new CalendarDate($calendar, 0, 7, 32), 10];

        // Year 1
        yield 'LinearCalendar LeapDays: First day of the first month of year 1' => [new CalendarDate($calendar, 1, 1, 1), 1];
        yield 'LinearCalendar LeapDays: Third day of the first month of year 1' => [new CalendarDate($calendar, 1, 1, 3), null];
        yield 'LinearCalendar LeapDays: Last day of the first month of year 1' => [new CalendarDate($calendar, 1, 1, 31), 10];

        // Year 1
        yield 'LinearCalendar LeapDays: First day of the seventh month of year 1' => [new CalendarDate($calendar, 1, 7, 1), 1];
        yield 'LinearCalendar LeapDays: Second day of the seventh month of year 1' => [new CalendarDate($calendar, 1, 7, 2), 2];
        yield 'LinearCalendar LeapDays: Last day of the seventh month of year 1' => [new CalendarDate($calendar, 1, 7, 31), 10];
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

        $calendar = ExampleCalendars::getLinearWithLeapDays();

        yield 'First day of the first month of year 0 in linear calendar' => [
            new CalendarDate($calendar, 0, 1, 1),
            1,
        ];

        yield 'Last day of the first month of year 0 in linear calendar' => [
            new CalendarDate($calendar, 0, 1, 30),
            30,
        ];

        yield 'Second day of the third month of year 2 in linear calendar' => [
            new CalendarDate($calendar, 2, 3, 2),
            794,
        ];

        yield 'Fifteenth day of the seventh month of year 0 in linear calendar' => [
            new CalendarDate($calendar, 7, 7, 15),
            2754,
        ];
    }

    #[Test]
    #[DataProvider('provideTotalDaysWithoutLeapDaysCountCases')]
    public function itCalculatesTheTotalDaysWithoutLeapDaysFromTheStartOfTheCalendar(CalendarDate $date, int $expectedDays): void
    {
        self::assertSame($expectedDays, $date->getTotalDaysFromCalendarStart(true));
    }

    public static function provideTotalDaysWithoutLeapDaysCountCases(): Generator
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

        $calendar = ExampleCalendars::getLinearWithLeapDays();

        yield 'First day of the first month of year 0 in linear calendar' => [
            new CalendarDate($calendar, 0, 1, 1),
            1,
        ];

        yield 'Last day of the first month of year 0 in linear calendar' => [
            new CalendarDate($calendar, 0, 1, 31),
            30,
        ];

        yield 'Last day of year 0 in linear calendar' => [
            new CalendarDate($calendar, 0, 12, 30),
            360,
        ];

        yield 'Second day of the third month of year 2 in linear calendar' => [
            new CalendarDate($calendar, 2, 3, 2),
            782,
        ];

        yield 'First day of the seventh month of year 7 in linear calendar' => [
            new CalendarDate($calendar, 7, 7, 1),
            2701,
        ];

        yield 'Fifteenth day of the seventh month of year 7 in linear calendar' => [
            new CalendarDate($calendar, 7, 7, 15),
            2715,
        ];

        yield 'Last day of the seventh month of year 7 in linear calendar' => [
            new CalendarDate($calendar, 7, 7, 31),
            2730,
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

        $calendar = ExampleCalendars::getLinearWithLeapDays();

        yield 'Linear Calendar - subtract a single day to a leap day' => [
            new CalendarDate($calendar, 0, 7, 3),
            1,
            'Shieldday Arthan 0 after the Flood',
        ];

        yield 'Linear Calendar - subtract a single day to a leap day that is inactive' => [
            new CalendarDate($calendar, 1, 7, 3),
            1,
            '2. Arthan 1 after the Flood',
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

    #[Test]
    #[DataProvider('provideDateFormattingCases')]
    public function ifFormatsADate(CalendarDate $date, string|null $format, string $expected): void
    {
        self::assertSame($expected, $date->format($format));
    }

    public static function provideDateFormattingCases(): Generator
    {
        $calendar = ExampleCalendars::getFullFeatured();
        $date     = new CalendarDate($calendar, 1, 3, 10);

        yield 'Default format' => [
            $date,
            null,
            '10. ThirdMonth 1 after Boom',
        ];

        yield 'Custom format with day only' => [
            $date,
            '%d',
            '10',
        ];

        yield 'Custom format with month name only' => [
            $date,
            '%M',
            'ThirdMonth',
        ];

        yield 'Custom format with year and epoch' => [
            $date,
            '%Y',
            '1 after Boom',
        ];

        $date = new CalendarDate(ExampleCalendars::getLinearWithLeapDays(), 0, 7, 2);

        yield 'Default format for leap day' => [
            $date,
            null,
            'Shieldday 0 after the Flood',
        ];

        yield 'Custom format with leap day' => [
            $date,
            '%D %M %y',
            'Shieldday Arthan 0',
        ];
    }

    #[Test]
    #[DataProvider('provideLeapDayCountCases')]
    public function itCanCalculateTheAmountOfLeapDaysBetweenTwoDates(
        CalendarDate $fromDate,
        CalendarDate $toDate,
        int $expectedLeapDayCount,
    ): void {
        self::assertSame($expectedLeapDayCount, $fromDate->countLeapDaysBetween($toDate));
    }

    public static function provideLeapDayCountCases(): Generator
    {
        $calendar = ExampleCalendars::getLinearWithLeapDays();

        yield 'Linear Calendar - Leap day count within a single month' => [
            new CalendarDate($calendar, 0, 1, 1),
            new CalendarDate($calendar, 0, 1, 30),
            1,
        ];

        yield 'Linear Calendar - Leap day count in year 0' => [
            new CalendarDate($calendar, 0, 1, 1),
            new CalendarDate($calendar, 0, 12, 30),
            6,
        ];

        yield 'Linear Calendar - Leap day count in year 1' => [
            new CalendarDate($calendar, 1, 1, 1),
            new CalendarDate($calendar, 1, 12, 30),
            5,
        ];
    }
}
