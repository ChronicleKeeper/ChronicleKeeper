<?php

declare(strict_types=1);

namespace ChronicleKeeper\Calendar\Application\Query;

use ChronicleKeeper\Calendar\Domain\Entity\Calendar;
use ChronicleKeeper\Calendar\Domain\Entity\Calendar\DayCollection;
use ChronicleKeeper\Calendar\Domain\Entity\Calendar\LeapDay;
use ChronicleKeeper\Calendar\Domain\Entity\Calendar\Month;
use ChronicleKeeper\Calendar\Domain\Entity\Calendar\WeekConfiguration;
use ChronicleKeeper\Calendar\Domain\Entity\Calendar\WeekDay;
use ChronicleKeeper\Shared\Application\Query\Query;
use ChronicleKeeper\Shared\Application\Query\QueryParameters;

final class LoadCalendarQuery implements Query
{
    public function query(QueryParameters $parameters): Calendar
    {
        $calendar = new Calendar();

        // Configure Months
        $calendar->setMonths(
            new Month($calendar, 1, 'Januar', new DayCollection(
                30,
                // TODO LEAP days have to be settable within the month
                new LeapDay(31, 'Drei Könige'),
                new LeapDay(32, 'Vier Könige'),
            )),
            new Month($calendar, 2, 'Februar', new DayCollection(
                30,
                new LeapDay(31, 'Heiliger Schalttag'),
            )),
            new Month($calendar, 3, 'März', new DayCollection(30)),
            new Month($calendar, 4, 'April', new DayCollection(30)),
        );

        // Configure Weeks
        $calendar->setWeekConfiguration(new WeekConfiguration(
            new WeekDay(1, 'Ersttag'),
            new WeekDay(2, 'Zweittag'),
            new WeekDay(3, 'Drittag'),
            new WeekDay(4, 'Vierttag'),
            new WeekDay(5, 'Fünfttag'),
        ));

        return $calendar;
    }
}
