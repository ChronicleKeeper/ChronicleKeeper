<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Calendar\Domain\Entity\Fixture;

use ChronicleKeeper\Settings\Domain\ValueObject\Settings\CalendarSettings\CurrentDay;
use ChronicleKeeper\Settings\Domain\ValueObject\Settings\CalendarSettings\EpochSettings;
use ChronicleKeeper\Settings\Domain\ValueObject\Settings\CalendarSettings\LeapDaySettings;
use ChronicleKeeper\Settings\Domain\ValueObject\Settings\CalendarSettings\MonthSettings;
use ChronicleKeeper\Settings\Domain\ValueObject\Settings\CalendarSettings\WeekSettings;

final class LinearWithLeapDaysCalendarSettingsBuilder extends DefaultCalendarSettingsBuilder
{
    public function __construct()
    {
        $this->currentDay    = new CurrentDay(2163, 6, 7);
        $this->moonCycleDays = 30;
        $this->isFinished    = true;

        // Create all months with their leap days
        $this->months = [
            new MonthSettings(1, 'Taranis', 30, [new LeapDaySettings(3, 'Mithwinter')]),
            new MonthSettings(2, 'Imbolc', 30),
            new MonthSettings(3, 'Brigid', 30),
            new MonthSettings(4, 'Lughnasad', 30, [new LeapDaySettings(18, 'Firstseed')]),
            new MonthSettings(5, 'Beltain', 30),
            new MonthSettings(6, 'Litha', 30),
            new MonthSettings(7, 'Arthan', 30, [
                new LeapDaySettings(2, 'Shieldday', 4),
                new LeapDaySettings(21, 'Midsummer'),
            ]),
            new MonthSettings(8, 'Telisias', 30),
            new MonthSettings(9, 'Mabon', 30, [new LeapDaySettings(27, 'Highharvest')]),
            new MonthSettings(10, 'Cerun', 30),
            new MonthSettings(11, 'Sawuin', 30, [new LeapDaySettings(15, 'Moonfeast')]),
            new MonthSettings(12, 'Nox', 30),
        ];

        // Create epochs
        $this->epochs = [
            new EpochSettings('after the Flood', 0, null),
        ];

        // Create weeks
        $this->weeks = [
            new WeekSettings(1, 'Firstday'),
            new WeekSettings(2, 'Secondday'),
            new WeekSettings(3, 'Thirdday'),
            new WeekSettings(4, 'Fourthday'),
            new WeekSettings(5, 'Fithday'),
            new WeekSettings(6, 'Sixthday'),
            new WeekSettings(7, 'Seventhday'),
            new WeekSettings(8, 'Eigthday'),
            new WeekSettings(9, 'Ninthday'),
            new WeekSettings(10, 'Tenthday'),
        ];
    }
}
