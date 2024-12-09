<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Calendar\Domain\Entity;

use ChronicleKeeper\Calendar\Domain\Entity\Calendar;
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

    private function getCalendar(): Calendar
    {
        return FullExampleCalendar::get();
    }
}
