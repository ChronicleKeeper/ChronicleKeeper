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
use ChronicleKeeper\Test\Calendar\Domain\Entity\Fixture\FullExampleCalendar;
use PHPUnit\Framework\Attributes\CoversClass;
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

        self::assertSame('FirstMonth', $calendar->getMonthOfTheYear(1)->name);
        self::assertSame('SecondMonth', $calendar->getMonthOfTheYear(2)->name);
        self::assertSame('ThirdMonth', $calendar->getMonthOfTheYear(3)->name);
    }

    #[Test]
    public function itCanCountTheDaysInAYear(): void
    {
        $calendar = $this->getCalendar();
        self::assertSame(35, $calendar->countDaysInYear(1));
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
        $calendar->getMonthOfTheYear(4);
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
        return FullExampleCalendar::get();
    }
}
