<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Calendar\Domain\Entity\Fixture;

use ChronicleKeeper\Settings\Domain\ValueObject\Settings\CalendarSettings;
use ChronicleKeeper\Settings\Domain\ValueObject\Settings\CalendarSettings\EpochSettings;
use ChronicleKeeper\Settings\Domain\ValueObject\Settings\CalendarSettings\MonthSettings;
use ChronicleKeeper\Settings\Domain\ValueObject\Settings\CalendarSettings\WeekSettings;

abstract class DefaultCalendarSettingsBuilder
{
    /** @var array<MonthSettings> */
    protected array $months = [];

    /** @var array<EpochSettings> */
    protected array $epochs = [];

    /** @var array<WeekSettings> */
    protected array $weeks = [];

    protected float $moonCycleDays = 30;
    protected bool $isFinished     = true;

    public function build(): CalendarSettings
    {
        return new CalendarSettings(
            $this->moonCycleDays,
            $this->isFinished,
            $this->months,
            $this->epochs,
            $this->weeks,
        );
    }
}
