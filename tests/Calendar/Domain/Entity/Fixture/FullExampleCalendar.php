<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Calendar\Domain\Entity\Fixture;

use ChronicleKeeper\Calendar\Domain\Entity\Calendar;
use ChronicleKeeper\Calendar\Domain\Entity\Calendar\Configuration;
use ChronicleKeeper\Calendar\Domain\Entity\Calendar\DayCollection;
use ChronicleKeeper\Calendar\Domain\Entity\Calendar\Epoch;
use ChronicleKeeper\Calendar\Domain\Entity\Calendar\EpochCollection;
use ChronicleKeeper\Calendar\Domain\Entity\Calendar\Month;

class FullExampleCalendar
{
    public static function get(): Calendar
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
}
