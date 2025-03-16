<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Calendar\Domain\Entity\Fixture;

use ChronicleKeeper\Settings\Domain\ValueObject\Settings\CalendarSettings\CurrentDay;
use ChronicleKeeper\Settings\Domain\ValueObject\Settings\CalendarSettings\EpochSettings;
use ChronicleKeeper\Settings\Domain\ValueObject\Settings\CalendarSettings\MonthSettings;
use ChronicleKeeper\Settings\Domain\ValueObject\Settings\CalendarSettings\WeekSettings;

final class OnlyRegularDaysCalendarSettingsBuilder extends DefaultCalendarSettingsBuilder
{
    public function __construct()
    {
        $this->currentDay    = new CurrentDay(1985, 9, 11);
        $this->moonCycleDays = 29.5;
        $this->isFinished    = true;

        // Create months
        $this->months = [
            new MonthSettings(1, 'January', 31),
            new MonthSettings(2, 'February', 28),
            new MonthSettings(3, 'March', 31),
            new MonthSettings(4, 'April', 30),
            new MonthSettings(5, 'May', 31),
            new MonthSettings(6, 'June', 30),
            new MonthSettings(7, 'July', 31),
            new MonthSettings(8, 'August', 31),
            new MonthSettings(9, 'September', 30),
            new MonthSettings(10, 'October', 31),
            new MonthSettings(11, 'November', 30),
            new MonthSettings(12, 'December', 31),
        ];

        // Create epochs
        $this->epochs = [
            new EpochSettings('AD', 0, null),
        ];

        // Create weeks
        $this->weeks = [
            new WeekSettings(1, 'Monday'),
            new WeekSettings(2, 'Tuesday'),
            new WeekSettings(3, 'Wednesday'),
            new WeekSettings(4, 'Thursday'),
            new WeekSettings(5, 'Friday'),
            new WeekSettings(6, 'Saturday'),
            new WeekSettings(7, 'Sunday'),
        ];
    }
}
