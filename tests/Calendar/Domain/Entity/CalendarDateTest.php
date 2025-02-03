<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Calendar\Domain\Entity;

use ChronicleKeeper\Calendar\Domain\Entity\CalendarDate;
use ChronicleKeeper\Calendar\Domain\Exception\DayNotExistsInMonth;
use ChronicleKeeper\Calendar\Domain\Exception\MonthNotExists;
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
}
