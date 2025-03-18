<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Calendar\Domain\Entity\Fixture;

use ChronicleKeeper\Calendar\Domain\Entity\Calendar;
use ChronicleKeeper\Calendar\Domain\Entity\Calendar\Configuration;
use ChronicleKeeper\Calendar\Domain\Entity\Calendar\MoonCycle;

class ExampleCalendars
{
    public static function getFullFeatured(): Calendar
    {
        return new Calendar(
            new Configuration(),
            [
                ['index' => 1, 'name' => 'FirstMonth', 'days' => 10],
                ['index' => 2, 'name' => 'SecondMonth', 'days' => 15],
                ['index' => 3, 'name' => 'ThirdMonth', 'days' => 10],
            ],
            [
                ['name' => 'after Boom', 'startYear' => 0],
            ],
            [
                ['index' => 1, 'name' => 'Day'],
            ],
            new MoonCycle(30),
        );
    }

    public static function getOnlyRegularDays(float $moonCycleOffset = 0): Calendar
    {
        return new Calendar(
            new Configuration(beginsInYear: 0),
            [
                ['index' => 1, 'name' => 'January', 'days' => 31],
                ['index' => 2, 'name' => 'February', 'days' => 28],
                ['index' => 3, 'name' => 'March', 'days' => 31],
                ['index' => 4, 'name' => 'April', 'days' => 30],
                ['index' => 5, 'name' => 'May', 'days' => 31],
                ['index' => 6, 'name' => 'June', 'days' => 30],
                ['index' => 7, 'name' => 'July', 'days' => 31],
                ['index' => 8, 'name' => 'August', 'days' => 31],
                ['index' => 9, 'name' => 'September', 'days' => 30],
                ['index' => 10, 'name' => 'October', 'days' => 31],
                ['index' => 11, 'name' => 'November', 'days' => 30],
                ['index' => 12, 'name' => 'December', 'days' => 31],
            ],
            [
                ['name' => 'AD', 'startYear' => 0],
            ],
            [
                ['index' => 1, 'name' => 'Monday'],
                ['index' => 2, 'name' => 'Tuesday'],
                ['index' => 3, 'name' => 'Wednesday'],
                ['index' => 4, 'name' => 'Thursday'],
                ['index' => 5, 'name' => 'Friday'],
                ['index' => 6, 'name' => 'Saturday'],
                ['index' => 7, 'name' => 'Sunday'],
            ],
            new MoonCycle(29.5, $moonCycleOffset),
        );
    }

    public static function getLinearWithLeapDays(float $moonCycleOffset = 0): Calendar
    {
        return new Calendar(
            new Configuration(beginsInYear: 0),
            [
                [
                    'index' => 1,
                    'name' => 'Taranis',
                    'days' => 30,
                    'leapDays' => [
                        ['day' => 3, 'name' => 'Mithwinter'],
                    ],
                ],
                [
                    'index' => 2,
                    'name' => 'Imbolc',
                    'days' => 30,
                ],
                [
                    'index' => 3,
                    'name' => 'Brigid',
                    'days' => 30,
                ],
                [
                    'index' => 4,
                    'name' => 'Lughnasad',
                    'days' => 30,
                    'leapDays' => [
                        ['day' => 18, 'name' => 'Firstseed'],
                    ],
                ],
                [
                    'index' => 5,
                    'name' => 'Beltain',
                    'days' => 30,
                ],
                [
                    'index' => 6,
                    'name' => 'Litha',
                    'days' => 30,
                ],
                [
                    'index' => 7,
                    'name' => 'Arthan',
                    'days' => 30,
                    'leapDays' => [
                        ['day' => 2, 'name' => 'Shieldday', 'yearInterval' => 4],
                        ['day' => 21, 'name' => 'Midsummer'],
                    ],
                ],
                [
                    'index' => 8,
                    'name' => 'Telisias',
                    'days' => 30,
                ],
                [
                    'index' => 9,
                    'name' => 'Mabon',
                    'days' => 30,
                    'leapDays' => [
                        ['day' => 27, 'name' => 'Highharvest'],
                    ],
                ],
                [
                    'index' => 10,
                    'name' => 'Cerun',
                    'days' => 30,
                ],
                [
                    'index' => 11,
                    'name' => 'Sawuin',
                    'days' => 30,
                    'leapDays' => [
                        ['day' => 15, 'name' => 'Moonfeast'],
                    ],
                ],
                [
                    'index' => 12,
                    'name' => 'Nox',
                    'days' => 30,
                ],
            ],
            [
                ['name' => 'after the Flood', 'startYear' => 0],
            ],
            [
                ['index' => 1, 'name' => 'Firstday'],
                ['index' => 2, 'name' => 'Secondday'],
                ['index' => 3, 'name' => 'Thirdday'],
                ['index' => 4, 'name' => 'Fourthday'],
                ['index' => 5, 'name' => 'Fithday'],
                ['index' => 6, 'name' => 'Sixthday'],
                ['index' => 7, 'name' => 'Seventhday'],
                ['index' => 8, 'name' => 'Eigthday'],
                ['index' => 9, 'name' => 'Ninthday'],
                ['index' => 10, 'name' => 'Tenthday'],
            ],
            new MoonCycle(30, $moonCycleOffset),
        );
    }

    public static function getCalendarWithLeapDayAsFirstDayOfTheYear(): Calendar
    {
        return new Calendar(
            new Configuration(beginsInYear: 0),
            [
                [
                    'index' => 1,
                    'name' => 'First',
                    'days' => 10,
                    'leapDays' => [
                        ['day' => 1, 'name' => 'Happy New Year!'],
                    ],
                ],
                ['index' => 2, 'name' => 'Second', 'days' => 15],
                [
                    'index' => 3,
                    'name' => 'Third',
                    'days' => 20,
                    'leapDays' => [
                        ['day' => 21, 'name' => 'Good Bye Year!'],
                    ],
                ],
            ],
            [
                ['name' => 'AD', 'startYear' => 0],
            ],
            [
                ['index' => 1, 'name' => 'First Day'],
                ['index' => 2, 'name' => 'Second Day'],
                ['index' => 3, 'name' => 'Party Day'],
            ],
            new MoonCycle(10),
        );
    }
}
