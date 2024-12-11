<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Calendar\Domain\Entity;

use ChronicleKeeper\Calendar\Domain\Entity\Calendar;
use ChronicleKeeper\Calendar\Domain\Entity\Calendar\Month;
use ChronicleKeeper\Calendar\Domain\Exception\YearHasNotASequentialListOfMonths;
use ChronicleKeeper\Calendar\Domain\Exception\YearIsNotStartingWithFirstMonth;
use ChronicleKeeper\Test\Calendar\Domain\Entity\Fixture\FullExampleCalendar;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(Calendar::class)]
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
        self::assertSame(35, $calendar->countDaysInYear());
    }

    #[Test]
    public function itIsWorkingWithAnEmptyListOfMonths(): void
    {
        $calendar = new Calendar();
        self::assertSame(0, $calendar->countDaysInYear());
    }

    #[Test]
    public function itFailsSettingMonthsWithIndexNotStartingWithOne(): void
    {
        $this->expectException(YearIsNotStartingWithFirstMonth::class);

        $calendar = new Calendar();
        $calendar->setMonths(new Month($calendar, 2, 'SecondMonth', 15));
    }

    #[Test]
    public function itFailsSettingMonthsWithNonSequentialIndexes(): void
    {
        $this->expectException(YearHasNotASequentialListOfMonths::class);

        $calendar = new Calendar();
        $calendar->setMonths(
            new Month($calendar, 1, 'FirstMonth', 10),
            new Month($calendar, 3, 'ThirdMonth', 10),
            new Month($calendar, 4, 'SecondMonth', 15),
        );
    }

    private function getCalendar(): Calendar
    {
        return FullExampleCalendar::get();
    }
}
