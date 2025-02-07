<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Calendar\Domain\Entity;

use ChronicleKeeper\Calendar\Domain\Entity\Calendar;
use ChronicleKeeper\Calendar\Domain\Entity\Calendar\Configuration;
use ChronicleKeeper\Calendar\Domain\Entity\Calendar\DayCollection;
use ChronicleKeeper\Calendar\Domain\Entity\Calendar\Month;
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
    #[DataProvider('provideCountInYearCases')]
    public function itCanCountTheDaysInAYear(Calendar $calendar, int $year, int $expectedDays): void
    {
        self::assertSame($expectedDays, $calendar->countDaysInYear($year));
    }

    public static function provideCountInYearCases(): Generator
    {
        $calendar = ExampleCalendars::getOnlyRegularDays();

        yield 'Regular Days Only Calendar - Year 0' => [$calendar, 0, 365];
        yield 'Regular Days Only Calendar - Year 1' => [$calendar, 1, 365];
        yield 'Regular Days Only Calendar - Year 2' => [$calendar, 1, 365];
        yield 'Regular Days Only Calendar - Year 3' => [$calendar, 1, 365];
        yield 'Regular Days Only Calendar - Year 4' => [$calendar, 1, 365];

        $calendar = ExampleCalendars::getLinearWithLeapDays();

        yield 'Linear Calendar with Leap Days - Year 0' => [$calendar, 0, 366];
        yield 'Linear Calendar with Leap Days - Year 1' => [$calendar, 1, 365];
        yield 'Linear Calendar with Leap Days - Year 2' => [$calendar, 2, 365];
        yield 'Linear Calendar with Leap Days - Year 3' => [$calendar, 3, 365];
        yield 'Linear Calendar with Leap Days - Year 4' => [$calendar, 4, 366];
    }

    #[Test]
    public function itIsWorkingWithAnEmptyListOfMonths(): void
    {
        $calendar = new Calendar(new Configuration());
        self::assertSame(0, $calendar->countDaysInYear(1));
    }

    #[Test]
    public function itFailsSettingMonthsWithIndexNotStartingWithOne(): void
    {
        $this->expectException(YearIsNotStartingWithFirstMonth::class);

        $calendar = new Calendar(new Configuration());
        $calendar->setMonths(new Month($calendar, 2, 'SecondMonth', new DayCollection(10)));
    }

    #[Test]
    public function itFailsSettingMonthsWithNonSequentialIndexes(): void
    {
        $this->expectException(YearHasNotASequentialListOfMonths::class);

        $calendar = new Calendar(new Configuration());
        $calendar->setMonths(
            new Month($calendar, 1, 'FirstMonth', new DayCollection(10)),
            new Month($calendar, 3, 'ThirdMonth', new DayCollection(10)),
        );
    }

    #[Test]
    public function itFailsGettingNonExistentMonth(): void
    {
        $this->expectException(MonthNotExists::class);

        $calendar = $this->getCalendar();
        $calendar->getMonth(4);
    }

    #[Test]
    public function itFailsOverwritingTheAlreadySetMonths(): void
    {
        $calendar = $this->getCalendar();
        $calendar->setMonths();

        self::assertNotSame(0, $calendar->countDaysInYear(1));
    }

    private function getCalendar(): Calendar
    {
        return ExampleCalendars::getFullFeatured();
    }

    #[Test]
    #[DataProvider('provideDaysUpToYearCountCases')]
    public function itCountsDaysUpToYear(Calendar $calendar, int $year, int $expected): void
    {
        self::assertSame($expected, $calendar->getDaysUpToYear($year));
    }

    public static function provideDaysUpToYearCountCases(): Generator
    {
        $calendar = ExampleCalendars::getOnlyRegularDays();

        yield 'Year 1 in the regular days calendar' => [$calendar, 1, 365];
        yield 'Year 2 in the regular days calendar' => [$calendar, 2, 730];
        yield 'Year 3 in the regular days calendar' => [$calendar, 3, 1095];
        yield 'Year 4 in the regular days calendar' => [$calendar, 4, 1460];

        $calendar = ExampleCalendars::getLinearWithLeapDays();

        yield 'Year 1 in the linear calendar, with leap days' => [$calendar, 1, 366]; // Initially an extra leap day
        yield 'Year 2 in the linear calendar, with leap days' => [$calendar, 2, 731];
        yield 'Year 3 in the linear calendar, with leap days' => [$calendar, 3, 1096];
        yield 'Year 4 in the linear calendar, with leap days' => [$calendar, 4, 1461];
        yield 'Year 5 in the linear calendar, with leap days' => [$calendar, 5, 1827]; // Added a leap day from year 4
    }

    #[Test]
    #[DataProvider('provideDaysUpToMonthInYearCountCases')]
    public function itCountsDaysUpToMonthInYear(Calendar $calendar, int $year, int $month, int $expected): void
    {
        self::assertSame($expected, $calendar->getDaysUpToMonthInYear($year, $month));
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

        yield 'Year 0, Month 1 in the linear calendar, with leap days' => [$calendar, 0, 1, 0];
        yield 'Year 0, Month 2 in the linear calendar, with leap days' => [$calendar, 0, 2, 31];
        yield 'Year 0, Month 3 in the linear calendar, with leap days' => [$calendar, 0, 3, 61];
        yield 'Year 0, Month 7 in the linear calendar, with leap days' => [$calendar, 0, 7, 182];
        yield 'Year 0, Month 8 in the linear calendar, with leap days' => [$calendar, 0, 8, 214];
        yield 'Year 0, Month 10 in the linear calendar, with leap days' => [$calendar, 0, 10, 275];
        yield 'Year 0, Month 12 in the linear calendar, with leap days' => [$calendar, 0, 12, 336];
        yield 'Year 1, Month 1 in the linear calendar, with leap days' => [$calendar, 1, 1, 366];
        yield 'Year 1, Month 5 in the linear calendar, with leap days' => [$calendar, 1, 5, 488];
        yield 'Year 2, Month 1 in the linear calendar, with leap days' => [$calendar, 2, 1, 731];
        yield 'Year 2, Month 4 in the linear calendar, with leap days' => [$calendar, 2, 4, 822];
        yield 'Year 6, Month 1 in the linear calendar, with leap days' => [$calendar, 6, 1, 2192];
        yield 'Year 12, Month 4 in the linear calendar, with leap days' => [$calendar, 12, 4, 4474];
    }

    #[Test]
    #[DataProvider('provideCountDaysInMonthInYearCases')]
    public function itCountsDaysInMonthInYear(Calendar $calendar, int $year, int $month, int $expected): void
    {
        self::assertSame($expected, $calendar->countDaysInMonth($year, $month));
    }

    public static function provideCountDaysInMonthInYearCases(): Generator
    {
        $calendar = ExampleCalendars::getOnlyRegularDays();

        yield 'Year 0, Month 1 in the regular days calendar' => [$calendar, 0, 1, 31];
        yield 'Year 0, Month 2 in the regular days calendar' => [$calendar, 0, 2, 28];
        yield 'Year 0, Month 3 in the regular days calendar' => [$calendar, 0, 3, 31];
        yield 'Year 0, Month 7 in the regular days calendar' => [$calendar, 0, 7, 31];
        yield 'Year 0, Month 8 in the regular days calendar' => [$calendar, 0, 8, 31];
        yield 'Year 0, Month 10 in the regular days calendar' => [$calendar, 0, 10, 31];
        yield 'Year 0, Month 12 in the regular days calendar' => [$calendar, 0, 12, 31];
        yield 'Year 1, Month 1 in the regular days calendar' => [$calendar, 1, 1, 31];
        yield 'Year 1, Month 5 in the regular days calendar' => [$calendar, 1, 5, 31];
        yield 'Year 2, Month 1 in the regular days calendar' => [$calendar, 2, 1, 31];
        yield 'Year 2, Month 4 in the regular days calendar' => [$calendar, 2, 4, 30];
        yield 'Year 6, Month 1 in the regular days calendar' => [$calendar, 6, 1, 31];
        yield 'Year 12, Month 4 in the regular days calendar' => [$calendar, 12, 4, 30];

        $calendar = ExampleCalendars::getLinearWithLeapDays();

        yield 'Year 0, Month 1 in the linear calendar, with leap days' => [$calendar, 0, 1, 31];
        yield 'Year 0, Month 2 in the linear calendar, with leap days' => [$calendar, 0, 2, 30];
        yield 'Year 0, Month 3 in the linear calendar, with leap days' => [$calendar, 0, 3, 30];
        yield 'Year 0, Month 7 in the linear calendar, with leap days' => [$calendar, 0, 7, 32];
        yield 'Year 0, Month 8 in the linear calendar, with leap days' => [$calendar, 0, 8, 30];
        yield 'Year 0, Month 10 in the linear calendar, with leap days' => [$calendar, 0, 10, 30];
        yield 'Year 0, Month 12 in the linear calendar, with leap days' => [$calendar, 0, 12, 30];
        yield 'Year 1, Month 1 in the linear calendar, with leap days' => [$calendar, 1, 1, 31];
        yield 'Year 1, Month 5 in the linear calendar, with leap days' => [$calendar, 1, 5, 30];
        yield 'Year 1, Month 7 in the linear calendar, with leap days' => [$calendar, 1, 7, 31];
        yield 'Year 2, Month 1 in the linear calendar, with leap days' => [$calendar, 2, 1, 31];
        yield 'Year 2, Month 4 in the linear calendar, with leap days' => [$calendar, 2, 4, 31];
        yield 'Year 6, Month 1 in the linear calendar, with leap days' => [$calendar, 6, 1, 31];
        yield 'Year 6, Month 7 in the linear calendar, with leap days' => [$calendar, 6, 1, 31];
        yield 'Year 12, Month 4 in the linear calendar, with leap days' => [$calendar, 12, 4, 31];
        yield 'Year 12, Month 7 in the linear calendar, with leap days' => [$calendar, 12, 7, 32];
    }
}
