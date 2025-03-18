<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Calendar\Domain\Entity\Fixture;

use ChronicleKeeper\Settings\Domain\ValueObject\Settings\CalendarSettings;
use ChronicleKeeper\Settings\Domain\ValueObject\Settings\CalendarSettings\CurrentDay;
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

    protected string $moonName       = 'Moon';
    protected float $moonCycleDays   = 30;
    protected float $moonCycleOffset = 0;

    protected bool $isFinished = true;

    protected CurrentDay|null $currentDay = null;

    public function build(): CalendarSettings
    {
        return new CalendarSettings(
            $this->moonName,
            $this->moonCycleDays,
            $this->moonCycleOffset,
            $this->isFinished,
            $this->months,
            $this->epochs,
            $this->weeks,
            $this->currentDay,
        );
    }
}
