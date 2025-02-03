<?php

declare(strict_types=1);

namespace ChronicleKeeper\Calendar\Application\Query;

use ChronicleKeeper\Calendar\Domain\Entity\Calendar;
use ChronicleKeeper\Calendar\Domain\Entity\Calendar\Configuration;
use ChronicleKeeper\Calendar\Domain\Entity\Calendar\DayCollection;
use ChronicleKeeper\Calendar\Domain\Entity\Calendar\Epoch;
use ChronicleKeeper\Calendar\Domain\Entity\Calendar\EpochCollection;
use ChronicleKeeper\Calendar\Domain\Entity\Calendar\Month;
use ChronicleKeeper\Calendar\Domain\Entity\Calendar\MoonCycle;
use ChronicleKeeper\Calendar\Domain\Entity\Calendar\WeekConfiguration;
use ChronicleKeeper\Calendar\Domain\Entity\Calendar\WeekDay;
use ChronicleKeeper\Shared\Application\Query\Query;
use ChronicleKeeper\Shared\Application\Query\QueryParameters;

final class LoadCalendarQuery implements Query
{
    public function query(QueryParameters $parameters): Calendar
    {
        $calendar = new Calendar(new Configuration());

        // Configure Months
        $calendar->setMonths(
            new Month($calendar, 1, 'Taranis', new DayCollection(25)),
            new Month($calendar, 2, 'Imbolc', new DayCollection(30)),
            new Month($calendar, 3, 'Brigid', new DayCollection(25)),
            new Month($calendar, 4, 'Lughnasad', new DayCollection(25)),
            new Month($calendar, 5, 'Beltain', new DayCollection(30)),
            new Month($calendar, 6, 'Litha', new DayCollection(30)),
            new Month($calendar, 7, 'Arthan', new DayCollection(
                30,
            )),
            new Month($calendar, 8, 'Telisias', new DayCollection(30)),
            new Month($calendar, 9, 'Mabon', new DayCollection(
                30,
            )),
            new Month($calendar, 10, 'Cerun', new DayCollection(30)),
            new Month($calendar, 11, 'Sawuin', new DayCollection(
                30,
            )),
            new Month($calendar, 12, 'Nox', new DayCollection(30)),
        );

        // Configure Epochs
        $calendar->setEpochCollection(new EpochCollection(
            new Epoch('nach der Flut', 0),
        ));

        // Configure Weeks
        $calendar->setWeekConfiguration(new WeekConfiguration(
            new WeekDay(1, 'Ersttag'),
            new WeekDay(2, 'Zweittag'),
            new WeekDay(3, 'Drittag'),
            new WeekDay(4, 'Vierttag'),
            new WeekDay(5, 'Fünfttag'),
            new WeekDay(6, 'Sechsttag'),
            new WeekDay(7, 'Siebttag'),
            new WeekDay(8, 'Achsttag'),
            new WeekDay(9, 'Neunttag'),
            new WeekDay(10, 'Zehnttag'),
        ));

        // Configure Moon Cycle
        $calendar->setMoonCycle(new MoonCycle(30));

        return $calendar;
    }
}
