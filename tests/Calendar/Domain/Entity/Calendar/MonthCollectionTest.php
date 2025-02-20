<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Calendar\Domain\Entity\Calendar;

use ChronicleKeeper\Calendar\Domain\Entity\Calendar;
use ChronicleKeeper\Calendar\Domain\Entity\Calendar\Configuration;
use ChronicleKeeper\Calendar\Domain\Entity\Calendar\DayCollection;
use ChronicleKeeper\Calendar\Domain\Entity\Calendar\Month;
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
#[Small]
class MonthCollectionTest extends TestCase
{
    #[Test]
    public function itWorksWithEmptyMonthList(): void
    {
        $collection = new MonthCollection();
        self::assertCount(0, $collection);
    }

    #[Test]
    public function itFailsWhenMonthsDoNotStartWithOne(): void
    {
        $this->expectException(YearIsNotStartingWithFirstMonth::class);

        $calendar = new Calendar(new Configuration());
        new MonthCollection(
            new Month($calendar, 2, 'SecondMonth', new DayCollection(10)),
        );
    }

    #[Test]
    public function itFailsWhenMonthsAreNotSequential(): void
    {
        $this->expectException(YearHasNotASequentialListOfMonths::class);

        $calendar = new Calendar(new Configuration());
        new MonthCollection(
            new Month($calendar, 1, 'FirstMonth', new DayCollection(10)),
            new Month($calendar, 3, 'ThirdMonth', new DayCollection(10)),
        );
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
        $calendar = new Calendar(new Configuration());
        $month    = new Month($calendar, 1, 'FirstMonth', new DayCollection(10));

        $collection = new MonthCollection($month);

        self::assertSame($month, $collection->get(1));
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
    public function itIsWorkingWithAnEmptyListOfMonths(): void
    {
        $calendar = new Calendar(new Configuration());
        self::assertSame(0, $calendar->getMonths()->countDaysInYear(1));
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
}
