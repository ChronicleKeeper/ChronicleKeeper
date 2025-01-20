<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Calendar\Domain\Entity\Fixture;

use ChronicleKeeper\Calendar\Domain\Entity\Calendar;
use ChronicleKeeper\Calendar\Domain\Entity\Calendar\DayCollection;
use ChronicleKeeper\Calendar\Domain\Entity\Calendar\LeapDay;
use ChronicleKeeper\Calendar\Domain\Entity\Calendar\Month;

class FullExampleCalendar
{
    public static function get(): Calendar
    {
        $calendar = new Calendar();

        $dayCollection = new DayCollection(
            10,
            new LeapDay(11, 'EndOfYearFirstLeapDay'),
            new LeapDay(12, 'EndOfYearSecondLeapDay'),
        );

        $thirdMonth = new Month($calendar, 3, 'ThirdMonth', $dayCollection);

        $calendar->setMonths(
            new Month($calendar, 1, 'FirstMonth', new DayCollection(
                10,
                new LeapDay(1, 'NewYearsLeapDay'),
            )),
            $thirdMonth,
            new Month($calendar, 2, 'SecondMonth', new DayCollection(15)),
        );

        return $calendar;
    }
}
