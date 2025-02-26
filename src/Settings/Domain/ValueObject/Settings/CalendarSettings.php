<?php

declare(strict_types=1);

namespace ChronicleKeeper\Settings\Domain\ValueObject\Settings;

use ChronicleKeeper\Settings\Domain\ValueObject\Settings\CalendarSettings\EpochSettings;
use ChronicleKeeper\Settings\Domain\ValueObject\Settings\CalendarSettings\MonthSettings;
use ChronicleKeeper\Settings\Domain\ValueObject\Settings\CalendarSettings\WeekSettings;
use JsonSerializable;

/**
 * @phpstan-import-type MonthSettingsArray from MonthSettings
 * @phpstan-import-type EpochSettingsArray from EpochSettings
 * @phpstan-import-type WeekSettingsArray from WeekSettings
 * @phpstan-type CalendarSettingsArray = array{
 *     months: array<MonthSettingsArray>,
 *     epochs: array<EpochSettingsArray>,
 *     weeks: array<WeekSettingsArray>,
 * }
 */
class CalendarSettings implements JsonSerializable
{
    /**
     * @param array<MonthSettings> $months
     * @param array<EpochSettings> $epochs
     * @param array<WeekSettings>  $weeks
     */
    public function __construct(
        private readonly array $months = [],
        private readonly array $epochs = [],
        private readonly array $weeks = [],
    ) {
    }

    /** @param CalendarSettingsArray $array */
    public static function fromArray(array $array): self
    {
        $months = [];
        foreach ($array['months'] as $monthData) {
            $months[] = MonthSettings::fromArray($monthData);
        }

        $epochs = [];
        foreach ($array['epochs'] as $epochData) {
            $epochs[] = EpochSettings::fromArray($epochData);
        }

        $weeks = [];
        foreach ($array['weeks'] as $weekData) {
            $weeks[] = WeekSettings::fromArray($weekData);
        }

        return new self($months, $epochs, $weeks);
    }

    /** @return CalendarSettingsArray */
    public function toArray(): array
    {
        $months = [];
        foreach ($this->months as $month) {
            $months[] = $month->toArray();
        }

        $epochs = [];
        foreach ($this->epochs as $epoch) {
            $epochs[] = $epoch->toArray();
        }

        $weeks = [];
        foreach ($this->weeks as $week) {
            $weeks[] = $week->toArray();
        }

        return [
            'months' => $months,
            'epochs' => $epochs,
            'weeks' => $weeks,
        ];
    }

    /** @return CalendarSettingsArray */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    /** @return array<MonthSettings> */
    public function getMonths(): array
    {
        return $this->months;
    }

    /** @return array<EpochSettings> */
    public function getEpochs(): array
    {
        return $this->epochs;
    }

    /** @return array<WeekSettings> */
    public function getWeeks(): array
    {
        return $this->weeks;
    }
}
