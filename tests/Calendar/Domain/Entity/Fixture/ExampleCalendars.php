<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Calendar\Domain\Entity\Fixture;

use ChronicleKeeper\Calendar\Domain\Entity\Calendar;
use ChronicleKeeper\Calendar\Domain\Entity\Calendar\Configuration;
use ChronicleKeeper\Calendar\Domain\Entity\Calendar\DayCollection;
use ChronicleKeeper\Calendar\Domain\Entity\Calendar\Epoch;
use ChronicleKeeper\Calendar\Domain\Entity\Calendar\EpochCollection;
use ChronicleKeeper\Calendar\Domain\Entity\Calendar\Month;
use ChronicleKeeper\Calendar\Domain\Entity\Calendar\MoonCycle;
use ChronicleKeeper\Calendar\Domain\Entity\Calendar\WeekConfiguration;
use ChronicleKeeper\Calendar\Domain\Entity\Calendar\WeekDay;

class ExampleCalendars
{
    public static function getFullFeatured(): Calendar
    {
        $calendar = new Calendar(new Configuration());
        $calendar->setEpochCollection(new EpochCollection(new Epoch('after Boom', 0)));

        $dayCollection = new DayCollection(10);
        $thirdMonth    = new Month($calendar, 3, 'ThirdMonth', $dayCollection);

        $calendar->setMonths(
            new Month($calendar, 1, 'FirstMonth', new DayCollection(
                10,
            )),
            $thirdMonth,
            new Month($calendar, 2, 'SecondMonth', new DayCollection(15)),
        );

        return $calendar;
    }

    public static function getOnlyRegularDays(): Calendar
    {
        $calendar = new Calendar(new Configuration(beginsInYear: 0));
        $calendar->setEpochCollection(new EpochCollection(new Epoch('AD', 0)));
        $calendar->setMoonCycle(new MoonCycle(29.5));
        $calendar->setWeekConfiguration(new WeekConfiguration(
            new WeekDay(1, 'Monday'),
            new WeekDay(2, 'Tuesday'),
            new WeekDay(3, 'Wednesday'),
            new WeekDay(4, 'Thursday'),
            new WeekDay(5, 'Friday'),
            new WeekDay(6, 'Saturday'),
            new WeekDay(7, 'Sunday'),
        ));

        $calendar->setMonths(
            new Month($calendar, 1, 'January', new DayCollection(31)),
            new Month($calendar, 2, 'February', new DayCollection(28)),
            new Month($calendar, 3, 'March', new DayCollection(31)),
            new Month($calendar, 4, 'April', new DayCollection(30)),
            new Month($calendar, 5, 'May', new DayCollection(31)),
            new Month($calendar, 6, 'June', new DayCollection(30)),
            new Month($calendar, 7, 'July', new DayCollection(31)),
            new Month($calendar, 8, 'August', new DayCollection(31)),
            new Month($calendar, 9, 'September', new DayCollection(30)),
            new Month($calendar, 10, 'October', new DayCollection(31)),
            new Month($calendar, 11, 'November', new DayCollection(30)),
            new Month($calendar, 12, 'December', new DayCollection(31)),
        );

        return $calendar;
    }
}
