<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Calendar\Domain\Entity\Fixture;

use ChronicleKeeper\Settings\Domain\ValueObject\Settings\CalendarSettings\EpochSettings;
use ChronicleKeeper\Settings\Domain\ValueObject\Settings\CalendarSettings\LeapDaySettings;
use ChronicleKeeper\Settings\Domain\ValueObject\Settings\CalendarSettings\MonthSettings;
use ChronicleKeeper\Settings\Domain\ValueObject\Settings\CalendarSettings\WeekSettings;

final class LeapDayAsFirstDayCalendarSettingsBuilder extends DefaultCalendarSettingsBuilder
{
    public function __construct()
    {
        $this->moonCycleDays = 10;
        $this->isFinished    = true;

        // Create months
        $this->months = [
            new MonthSettings(1, 'First', 10, [new LeapDaySettings(1, 'Happy New Year!')]),
            new MonthSettings(2, 'Second', 15),
            new MonthSettings(3, 'Third', 20),
        ];

        // Create epochs
        $this->epochs = [
            new EpochSettings('AD', 0, null),
        ];

        // Create weeks
        $this->weeks = [
            new WeekSettings(1, 'First Day'),
            new WeekSettings(2, 'Second Day'),
            new WeekSettings(3, 'Party Day'),
        ];
    }
}
