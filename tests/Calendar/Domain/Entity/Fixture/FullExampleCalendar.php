<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Calendar\Domain\Entity\Fixture;

use ChronicleKeeper\Calendar\Domain\Entity\Calendar;
use ChronicleKeeper\Calendar\Domain\Entity\Calendar\LeapDay;
use ChronicleKeeper\Calendar\Domain\Entity\Calendar\Month;

class FullExampleCalendar
{
    public static function get(): Calendar
    {
        $calendar = new Calendar();

        $thirdMonth = new Month($calendar, 3, 'ThirdMonth', 10);
        $thirdMonth->setLeapDays(
            new LeapDay(11, 'EndOfYearFirstLeapDay'),
            new LeapDay(12, 'EndOfYearSecondLeapDay'),
        );

        $calendar->setMonths(
            new Month($calendar, 1, 'FirstMonth', 10),
            $thirdMonth,
            new Month($calendar, 2, 'SecondMonth', 15),
        );

        return $calendar;
    }
}
