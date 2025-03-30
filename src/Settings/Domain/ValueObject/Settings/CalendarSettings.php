<?php

declare(strict_types=1);

namespace ChronicleKeeper\Settings\Domain\ValueObject\Settings;

use ChronicleKeeper\Settings\Domain\ValueObject\Settings\CalendarSettings\CurrentDay;
use ChronicleKeeper\Settings\Domain\ValueObject\Settings\CalendarSettings\EpochSettings;
use ChronicleKeeper\Settings\Domain\ValueObject\Settings\CalendarSettings\MonthSettings;
use ChronicleKeeper\Settings\Domain\ValueObject\Settings\CalendarSettings\WeekSettings;
use JsonSerializable;

use function count;

/**
 * @phpstan-import-type MonthSettingsArray from MonthSettings
 * @phpstan-import-type EpochSettingsArray from EpochSettings
 * @phpstan-import-type WeekSettingsArray from WeekSettings
 * @phpstan-import-type CurrentDayArray from CurrentDay
 * @phpstan-type CalendarSettingsArray = array{
 *     moons: list<array{
 *           moon_name: string,
 *           moon_cycle_days: float,
 *           moon_cycle_offset: float,
 *     }>,
 *     begins_in_year: int,
 *     is_finished?: bool,
 *     months: array<MonthSettingsArray>,
 *     epochs: array<EpochSettingsArray>,
 *     weeks: array<WeekSettingsArray>,
 *     current_day?: CurrentDayArray|null,
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
        private readonly int $beginsInYear = 0,
        private readonly string $moonName = 'Mond',
        private readonly float $moonCycleDays = 30,
        private readonly float $moonCycleOffset = 0,
        private readonly bool $isFinished = false,
        private readonly array $months = [],
        private readonly array $epochs = [],
        private readonly array $weeks = [],
        private readonly CurrentDay|null $currentDay = null,
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

        if (count($array['moons']) > 0) {
            $moon            = $array['moons'][0];
            $moonName        = $moon['moon_name'];
            $moonCycleDays   = $moon['moon_cycle_days'];
            $moonCycleOffset = $moon['moon_cycle_offset'];
        } else {
            $moonName        = 'Mond';
            $moonCycleDays   = 30;
            $moonCycleOffset = 0;
        }

        return new self(
            $array['begins_in_year'],
            $moonName,
            $moonCycleDays,
            $moonCycleOffset,
            $array['is_finished'] ?? false,
            $months,
            $epochs,
            $weeks,
            isset($array['current_day']) ? CurrentDay::fromArray($array['current_day']) : null,
        );
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
            'moons' => [
                [
                    'moon_name' => $this->moonName,
                    'moon_cycle_days' => $this->moonCycleDays,
                    'moon_cycle_offset' => $this->moonCycleOffset,
                ],
            ],
            'begins_in_year' => $this->beginsInYear,
            'is_finished' => $this->isFinished,
            'months' => $months,
            'epochs' => $epochs,
            'weeks' => $weeks,
            'current_day' => $this->currentDay?->toArray(),
        ];
    }

    /** @return CalendarSettingsArray */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    public function isFinished(): bool
    {
        return $this->isFinished;
    }

    public function getBeginsInYear(): int
    {
        return $this->beginsInYear;
    }

    public function getMoonCycleDays(): float
    {
        return $this->moonCycleDays;
    }

    public function getMoonName(): string
    {
        return $this->moonName;
    }

    public function getMoonCycleOffset(): float
    {
        return $this->moonCycleOffset;
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

    public function getCurrentDay(): CurrentDay|null
    {
        return $this->currentDay;
    }

    public function withCurrentDay(CurrentDay $day): self
    {
        return new self(
            $this->beginsInYear,
            $this->moonName,
            $this->moonCycleDays,
            $this->moonCycleOffset,
            $this->isFinished,
            $this->months,
            $this->epochs,
            $this->weeks,
            $day,
        );
    }
}
