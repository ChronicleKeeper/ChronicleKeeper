<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Calendar\Domain\Entity;

use ChronicleKeeper\Calendar\Domain\Entity\Calendar;
use ChronicleKeeper\Calendar\Domain\Exception\MonthNotExists;
use ChronicleKeeper\Calendar\Domain\Exception\YearHasNotASequentialListOfMonths;
use ChronicleKeeper\Calendar\Domain\Exception\YearIsNotStartingWithFirstMonth;
use ChronicleKeeper\Test\Calendar\Domain\Entity\Fixture\ExampleCalendars;
use Generator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(Calendar::class)]
#[CoversClass(YearIsNotStartingWithFirstMonth::class)]
#[CoversClass(YearHasNotASequentialListOfMonths::class)]
#[CoversClass(MonthNotExists::class)]
#[Small]
class CalendarTest extends TestCase
{
    #[Test]
    public function itSortsMonthsByYearsIndex(): void
    {
        $calendar = $this->getCalendar();

        self::assertSame('FirstMonth', $calendar->getMonth(1)->name);
        self::assertSame('SecondMonth', $calendar->getMonth(2)->name);
        self::assertSame('ThirdMonth', $calendar->getMonth(3)->name);
    }

    #[Test]
    public function itFailsGettingNonExistentMonth(): void
    {
        $this->expectException(MonthNotExists::class);

        $calendar = $this->getCalendar();
        $calendar->getMonth(4);
    }

    private function getCalendar(): Calendar
    {
        return ExampleCalendars::getFullFeatured();
    }

    #[Test]
    #[DataProvider('provideDaysUpToYearCountCases')]
    public function itCountsDaysUpToYear(
        Calendar $calendar,
        int $year,
        int $expectedRegularDays,
        int $expectedLeapDays,
    ): void {
        self::assertSame(
            ['days' => $expectedRegularDays, 'leapDays' => $expectedLeapDays],
            $calendar->getDaysUpToYear($year),
        );
    }

    public static function provideDaysUpToYearCountCases(): Generator
    {
        $calendar = ExampleCalendars::getOnlyRegularDays();

        yield 'Year 1 in the regular days calendar' => [$calendar, 1, 365, 0];
        yield 'Year 2 in the regular days calendar' => [$calendar, 2, 730, 0];
        yield 'Year 3 in the regular days calendar' => [$calendar, 3, 1095, 0];
        yield 'Year 4 in the regular days calendar' => [$calendar, 4, 1460, 0];

        $calendar = ExampleCalendars::getLinearWithLeapDays();

        yield 'Year 1 in the linear calendar, with leap days' => [$calendar, 1, 366, 6]; // Initially an extra leap day
        yield 'Year 2 in the linear calendar, with leap days' => [$calendar, 2, 731, 11];
        yield 'Year 3 in the linear calendar, with leap days' => [$calendar, 3, 1096, 16];
        yield 'Year 4 in the linear calendar, with leap days' => [$calendar, 4, 1461, 21];
        yield 'Year 5 in the linear calendar, with leap days' => [$calendar, 5, 1827, 27]; // Added a leap day from year 4
    }

    #[Test]
    #[DataProvider('provideDaysUpToMonthInYearCountCases')]
    public function itCountsDaysUpToMonthInYear(
        Calendar $calendar,
        int $year,
        int $month,
        int $expectedDayCount,
        int $expectedLeapDayCount = 0,
    ): void {
        self::assertSame(
            ['days' => $expectedDayCount, 'leapDays' => $expectedLeapDayCount],
            $calendar->getDaysUpToMonthInYear($year, $month),
        );
    }

    public static function provideDaysUpToMonthInYearCountCases(): Generator
    {
        $calendar = ExampleCalendars::getOnlyRegularDays();

        yield 'Year 0, Month 1 in the regular days calendar' => [$calendar, 0, 1, 0];
        yield 'Year 0, Month 2 in the regular days calendar' => [$calendar, 0, 2, 31];
        yield 'Year 0, Month 3 in the regular days calendar' => [$calendar, 0, 3, 59];
        yield 'Year 0, Month 7 in the regular days calendar' => [$calendar, 0, 7, 181];
        yield 'Year 0, Month 8 in the regular days calendar' => [$calendar, 0, 8, 212];
        yield 'Year 0, Month 10 in the regular days calendar' => [$calendar, 0, 10, 273];
        yield 'Year 0, Month 12 in the regular days calendar' => [$calendar, 0, 12, 334];
        yield 'Year 1, Month 1 in the regular days calendar' => [$calendar, 1, 1, 365];
        yield 'Year 1, Month 5 in the regular days calendar' => [$calendar, 1, 5, 485];
        yield 'Year 2, Month 1 in the regular days calendar' => [$calendar, 2, 1, 730];
        yield 'Year 2, Month 4 in the regular days calendar' => [$calendar, 2, 4, 820];
        yield 'Year 6, Month 1 in the regular days calendar' => [$calendar, 6, 1, 2190];
        yield 'Year 12, Month 4 in the regular days calendar' => [$calendar, 12, 4, 4470];

        $calendar = ExampleCalendars::getLinearWithLeapDays();

        yield 'Year 0, Month 1 in the linear calendar, with leap days' => [$calendar, 0, 1, 0, 0];
        yield 'Year 0, Month 2 in the linear calendar, with leap days' => [$calendar, 0, 2, 31, 1];
        yield 'Year 0, Month 3 in the linear calendar, with leap days' => [$calendar, 0, 3, 61, 1];
        yield 'Year 0, Month 7 in the linear calendar, with leap days' => [$calendar, 0, 7, 182, 2];
        yield 'Year 0, Month 8 in the linear calendar, with leap days' => [$calendar, 0, 8, 214, 4];
        yield 'Year 0, Month 10 in the linear calendar, with leap days' => [$calendar, 0, 10, 275, 5];
        yield 'Year 0, Month 12 in the linear calendar, with leap days' => [$calendar, 0, 12, 336, 6];
        yield 'Year 1, Month 1 in the linear calendar, with leap days' => [$calendar, 1, 1, 366, 6];
        yield 'Year 1, Month 5 in the linear calendar, with leap days' => [$calendar, 1, 5, 488, 8];
        yield 'Year 2, Month 1 in the linear calendar, with leap days' => [$calendar, 2, 1, 731, 11];
        yield 'Year 2, Month 4 in the linear calendar, with leap days' => [$calendar, 2, 4, 822, 12];
        yield 'Year 6, Month 1 in the linear calendar, with leap days' => [$calendar, 6, 1, 2192, 32];
        yield 'Year 12, Month 4 in the linear calendar, with leap days' => [$calendar, 12, 4, 4474, 64];
    }
}
