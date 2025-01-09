<?php

declare(strict_types=1);

namespace ChronicleKeeper\Calendar\Application\Query;

use ChronicleKeeper\Calendar\Domain\Entity\Calendar;
use ChronicleKeeper\Shared\Application\Query\Query;
use ChronicleKeeper\Shared\Application\Query\QueryParameters;

final class LoadCalendarQuery implements Query
{
    public function query(QueryParameters $parameters): Calendar
    {
        $calendar = new Calendar();
        $calendar->setMonths(
            new Calendar\Month($calendar, 1, 'Januar', new Calendar\DayCollection(31)),
            new Calendar\Month($calendar, 2, 'Februar', new Calendar\DayCollection(
                28,
                new Calendar\LeapDay(29, 'Heiliger Schalttag'),
            )),
            new Calendar\Month($calendar, 3, 'März', new Calendar\DayCollection(31)),
            new Calendar\Month($calendar, 4, 'April', new Calendar\DayCollection(30)),
        );

        return $calendar;
    }
}
