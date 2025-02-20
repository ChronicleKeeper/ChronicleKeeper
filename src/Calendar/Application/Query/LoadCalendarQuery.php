<?php

declare(strict_types=1);

namespace ChronicleKeeper\Calendar\Application\Query;

use ChronicleKeeper\Calendar\Domain\Entity\Calendar;
use ChronicleKeeper\Calendar\Domain\Entity\Calendar\Configuration;
use ChronicleKeeper\Calendar\Domain\Entity\Calendar\MoonCycle;
use ChronicleKeeper\Shared\Application\Query\Query;
use ChronicleKeeper\Shared\Application\Query\QueryParameters;

final class LoadCalendarQuery implements Query
{
    public function query(QueryParameters $parameters): Calendar
    {
        return new Calendar(
            new Configuration(),
            [
                [
                    'index' => 1,
                    'name' => 'Taranis',
                    'days' => 25,
                    'leapDays' => [
                        ['day' => 21, 'name' => 'Mittwinter'],
                    ],
                ],
                ['index' => 2, 'name' => 'Imbolc', 'days' => 30],
                ['index' => 3, 'name' => 'Brigid', 'days' => 25],
                ['index' => 4, 'name' => 'Lughnasad', 'days' => 25],
                ['index' => 5, 'name' => 'Beltain', 'days' => 30],
                ['index' => 6, 'name' => 'Litha', 'days' => 30],
                ['index' => 7, 'name' => 'Arthan', 'days' => 30],
                ['index' => 8, 'name' => 'Telisias', 'days' => 30],
                ['index' => 9, 'name' => 'Mabon', 'days' => 30],
                ['index' => 10, 'name' => 'Cerun', 'days' => 30],
                ['index' => 11, 'name' => 'Sawuin', 'days' => 30],
                ['index' => 12, 'name' => 'Nox', 'days' => 30],
            ],
            [
                ['name' => 'nach der Flut', 'startYear' => 0],
            ],
            [
                ['index' => 1, 'name' => 'Ersttag'],
                ['index' => 2, 'name' => 'Zweittag'],
                ['index' => 3, 'name' => 'Drittag'],
                ['index' => 4, 'name' => 'Vierttag'],
                ['index' => 5, 'name' => 'Fünfttag'],
                ['index' => 6, 'name' => 'Sechsttag'],
                ['index' => 7, 'name' => 'Siebttag'],
                ['index' => 8, 'name' => 'Achsttag'],
                ['index' => 9, 'name' => 'Neunttag'],
                ['index' => 10, 'name' => 'Zehnttag'],
            ],
            new MoonCycle(30),
        );
    }
}
