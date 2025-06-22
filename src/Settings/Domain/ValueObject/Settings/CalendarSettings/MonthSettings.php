<?php

declare(strict_types=1);

namespace ChronicleKeeper\Settings\Domain\ValueObject\Settings\CalendarSettings;

use JsonSerializable;

/**
 * @phpstan-type LeapDaySettingsArray = array{
 *     day: int,
 *     name: string,
 *     year_interval?: int
 * }
 * @phpstan-type MonthSettingsArray = array{
 *     index: int,
 *     name: string,
 *     days: int<0, max>,
 *     leap_days?: array<array{
 *       day: int,
 *       name: string,
 *       year_interval?: int
 *    }>
 * }
 */
class MonthSettings implements JsonSerializable
{
    /**
     * @param int<0, max>            $days
     * @param array<LeapDaySettings> $leapDays
     */
    public function __construct(
        private readonly int $index,
        private readonly string $name,
        private readonly int $days,
        private readonly array $leapDays = [],
    ) {
    }

    /** @param MonthSettingsArray $array */
    public static function fromArray(array $array): self
    {
        $leapDays = [];
        if (isset($array['leap_days'])) {
            foreach ($array['leap_days'] as $leapDayData) {
                $leapDays[] = LeapDaySettings::fromArray($leapDayData);
            }
        }

        return new self(
            $array['index'],
            $array['name'],
            $array['days'],
            $leapDays,
        );
    }

    /** @return MonthSettingsArray */
    public function toArray(): array
    {
        $result = [
            'index' => $this->index,
            'name' => $this->name,
            'days' => $this->days,
        ];

        if ($this->leapDays !== []) {
            $leapDaysArray = [];
            foreach ($this->leapDays as $leapDay) {
                $leapDaysArray[] = $leapDay->toArray();
            }

            $result['leap_days'] = $leapDaysArray;
        }

        return $result;
    }

    /** @return MonthSettingsArray */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    public function getIndex(): int
    {
        return $this->index;
    }

    public function getName(): string
    {
        return $this->name;
    }

    /** @return int<0, max> */
    public function getDays(): int
    {
        return $this->days;
    }

    /** @return array<LeapDaySettings> */
    public function getLeapDays(): array
    {
        return $this->leapDays;
    }
}
