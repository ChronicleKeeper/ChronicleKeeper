<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Calendar\Domain\Entity\Calendar;

use ChronicleKeeper\Calendar\Domain\Entity\Calendar;
use ChronicleKeeper\Calendar\Domain\Entity\Calendar\MonthCollection;
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

#[CoversClass(MonthCollection::class)]
#[CoversClass(YearIsNotStartingWithFirstMonth::class)]
#[CoversClass(YearHasNotASequentialListOfMonths::class)]
#[CoversClass(MonthNotExists::class)]
#[Small]
class MonthCollectionTest extends TestCase
{
    #[Test]
    public function itFailsWhenMonthsDoNotStartWithOne(): void
    {
        $this->expectException(YearIsNotStartingWithFirstMonth::class);

        $calendar = ExampleCalendars::getOnlyRegularDays();
        new MonthCollection($calendar->getMonths()->get(2));
    }

    #[Test]
    public function itFailsWhenMonthsAreNotSequential(): void
    {
        $this->expectException(YearHasNotASequentialListOfMonths::class);

        $calendar = ExampleCalendars::getOnlyRegularDays();
        new MonthCollection($calendar->getMonths()->get(1), $calendar->getMonths()->get(3));
    }

    #[Test]
    public function itFailsWhenRequestingNonExistentMonth(): void
    {
        $this->expectException(MonthNotExists::class);

        $collection = new MonthCollection();
        $collection->get(1);
    }

    #[Test]
    public function itGetsMonthByIndex(): void
    {
        $calendar = ExampleCalendars::getOnlyRegularDays();
        $months   = new MonthCollection($calendar->getMonths()->get(1));

        $month = $months->get(1);
        self::assertSame('January', $month->name);
        self::assertCount(31, $month->days);
    }

    #[Test]
    #[DataProvider('provideCountInYearCases')]
    public function itCanCountTheDaysInAYear(Calendar $calendar, int $year, int $expectedDays): void
    {
        self::assertSame($expectedDays, $calendar->getMonths()->countDaysInYear($year));
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
    #[DataProvider('provideLeapDaysInYearCases')]
    public function itCanCountTheLeapDaysInAYear(Calendar $calendar, int $year, int $expectedLeapDays): void
    {
        self::assertSame($expectedLeapDays, $calendar->getMonths()->countLeapDaysInYear($year));
    }

    public static function provideLeapDaysInYearCases(): Generator
    {
        $calendar = ExampleCalendars::getOnlyRegularDays();

        yield 'Regular Days Only Calendar - Year 0' => [$calendar, 0, 0];
        yield 'Regular Days Only Calendar - Year 1' => [$calendar, 1, 0];
        yield 'Regular Days Only Calendar - Year 2' => [$calendar, 2, 0];
        yield 'Regular Days Only Calendar - Year 3' => [$calendar, 3, 0];
        yield 'Regular Days Only Calendar - Year 4' => [$calendar, 4, 0];

        $calendar = ExampleCalendars::getLinearWithLeapDays();

        yield 'Linear Calendar with Leap Days - Year 0' => [$calendar, 0, 6];
        yield 'Linear Calendar with Leap Days - Year 1' => [$calendar, 1, 5];
        yield 'Linear Calendar with Leap Days - Year 2' => [$calendar, 2, 5];
        yield 'Linear Calendar with Leap Days - Year 3' => [$calendar, 3, 5];
        yield 'Linear Calendar with Leap Days - Year 4' => [$calendar, 4, 6];
    }

    #[Test]
    public function testFromArray(): void
    {
        $monthsData = [
            [
                'index' => 1,
                'name' => 'January',
                'days' => 31,
                'leapDays' => [
                    ['day' => 29, 'name' => 'Leap Day', 'yearInterval' => 4],
                ],
            ],
            [
                'index' => 2,
                'name' => 'February',
                'days' => 28,
            ],
        ];

        $calendar = ExampleCalendars::getOnlyRegularDays();
        $months   = MonthCollection::fromArray($calendar, $monthsData);

        self::assertCount(2, $months->getAll());
        self::assertSame('January', $months->get(1)->name);
        self::assertSame('February', $months->get(2)->name);

        self::assertCount(32, $months->get(1)->days);
        self::assertCount(28, $months->get(2)->days);

        self::assertCount(1, $months->get(1)->days->getLeapDaysInYear(0));
        self::assertEmpty($months->get(1)->days->getLeapDaysInYear(1));
        self::assertEmpty($months->get(2)->days->getLeapDaysInYear(1));

        self::assertSame(60, $months->countDaysInYear(0));
        self::assertSame(59, $months->countDaysInYear(1));
    }
}
