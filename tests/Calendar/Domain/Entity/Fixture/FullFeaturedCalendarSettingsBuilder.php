<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Calendar\Domain\Entity\Fixture;

use ChronicleKeeper\Settings\Domain\ValueObject\Settings\CalendarSettings\CurrentDay;
use ChronicleKeeper\Settings\Domain\ValueObject\Settings\CalendarSettings\EpochSettings;
use ChronicleKeeper\Settings\Domain\ValueObject\Settings\CalendarSettings\MonthSettings;
use ChronicleKeeper\Settings\Domain\ValueObject\Settings\CalendarSettings\WeekSettings;

final class FullFeaturedCalendarSettingsBuilder extends DefaultCalendarSettingsBuilder
{
    public function __construct()
    {
        $this->moonCycleDays = 30;
        $this->isFinished    = true;

        // Create months
        $this->months = [
            new MonthSettings(1, 'FirstMonth', 10),
            new MonthSettings(2, 'SecondMonth', 15),
            new MonthSettings(3, 'ThirdMonth', 10),
        ];

        // Create epochs
        $this->epochs = [
            new EpochSettings('after Boom', 0, null),
        ];

        // Create weeks
        $this->weeks = [
            new WeekSettings(1, 'Day'),
        ];

        $this->currentDay = new CurrentDay(1, 1, 1);
    }
}
