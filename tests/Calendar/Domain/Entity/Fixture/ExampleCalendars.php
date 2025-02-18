<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Calendar\Domain\Entity\Fixture;

use ChronicleKeeper\Calendar\Domain\Entity\Calendar;
use ChronicleKeeper\Calendar\Domain\Entity\Calendar\Configuration;
use ChronicleKeeper\Calendar\Domain\Entity\Calendar\DayCollection;
use ChronicleKeeper\Calendar\Domain\Entity\Calendar\EpochCollection;
use ChronicleKeeper\Calendar\Domain\Entity\Calendar\Month;
use ChronicleKeeper\Calendar\Domain\Entity\Calendar\MoonCycle;
use ChronicleKeeper\Calendar\Domain\Entity\Calendar\WeekConfiguration;
use ChronicleKeeper\Calendar\Domain\ValueObject\Epoch;
use ChronicleKeeper\Calendar\Domain\ValueObject\LeapDay;
use ChronicleKeeper\Calendar\Domain\ValueObject\WeekDay;

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

    public static function getLinearWithLeapDays(): Calendar
    {
        $calendar = new Calendar(new Configuration(beginsInYear: 0));
        $calendar->setEpochCollection(new EpochCollection(new Epoch('after the Flood', 0)));
        $calendar->setMoonCycle(new MoonCycle(30));
        $calendar->setWeekConfiguration(new WeekConfiguration(
            new WeekDay(1, 'Firstday'),
            new WeekDay(2, 'Secondday'),
            new WeekDay(3, 'Thirdday'),
            new WeekDay(4, 'Fourthday'),
            new WeekDay(5, 'Fithday'),
            new WeekDay(6, 'Sixthday'),
            new WeekDay(7, 'Seventhday'),
            new WeekDay(8, 'Eigthday'),
            new WeekDay(9, 'Ninthday'),
            new WeekDay(10, 'Tenthday'),
        ));

        $calendar->setMonths(
            new Month($calendar, 1, 'Taranis', new DayCollection(
                30,
                new LeapDay(3, 'Mithwinter'),
            )),
            new Month($calendar, 2, 'Imbolc', new DayCollection(30)),
            new Month($calendar, 3, 'Brigid', new DayCollection(30)),
            new Month($calendar, 4, 'Lughnasad', new DayCollection(
                30,
                new LeapDay(18, 'Firstseed'),
            )),
            new Month($calendar, 5, 'Beltain', new DayCollection(30)),
            new Month($calendar, 6, 'Litha', new DayCollection(30)),
            new Month($calendar, 7, 'Arthan', new DayCollection(
                30,
                new LeapDay(2, 'Shieldday', yearInterval: 4),
                new LeapDay(21, 'Midsummer'),
            )),
            new Month($calendar, 8, 'Telisias', new DayCollection(30)),
            new Month($calendar, 9, 'Mabon', new DayCollection(
                30,
                new LeapDay(27, 'Highharvest'),
            )),
            new Month($calendar, 10, 'Cerun', new DayCollection(30)),
            new Month($calendar, 11, 'Sawuin', new DayCollection(
                30,
                new LeapDay(15, 'Moonfeast'),
            )),
            new Month($calendar, 12, 'Nox', new DayCollection(30)),
        );

        return $calendar;
    }

    public static function getCalendarWithLeapDayAsFirstDayOfTheYear(): Calendar
    {
        $calendar = new Calendar(new Configuration(beginsInYear: 0));
        $calendar->setEpochCollection(new EpochCollection(new Epoch('AD', 0)));
        $calendar->setMoonCycle(new MoonCycle(10));
        $calendar->setWeekConfiguration(new WeekConfiguration(
            new WeekDay(1, 'First Day'),
            new WeekDay(2, 'Second Day'),
            new WeekDay(3, 'Party Day'),
        ));

        $calendar->setMonths(
            new Month($calendar, 1, 'First', new DayCollection(
                10,
                new LeapDay(1, 'Happy New Year!'),
            )),
            new Month($calendar, 2, 'Second', new DayCollection(15)),
            new Month($calendar, 3, 'Third', new DayCollection(20)),
        );

        return $calendar;
    }
}
