<?php

declare(strict_types=1);

namespace ChronicleKeeper\Settings\Domain\ValueObject\Settings\CalendarSettings;

use JsonSerializable;

/**
 * @phpstan-type CurrentDayArray = array{
 *     year: int,
 *     month: int,
 *     day: int
 * }
 */
class CurrentDay implements JsonSerializable
{
    public function __construct(
        private readonly int $year,
        private readonly int $month,
        private readonly int $day,
    ) {
    }

    /** @param CurrentDayArray $array */
    public static function fromArray(array $array): self
    {
        return new self(
            $array['year'],
            $array['month'],
            $array['day'],
        );
    }

    /** @return CurrentDayArray */
    public function toArray(): array
    {
        return [
            'year' => $this->year,
            'month' => $this->month,
            'day' => $this->day,
        ];
    }

    /** @return CurrentDayArray */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    public function getYear(): int
    {
        return $this->year;
    }

    public function getMonth(): int
    {
        return $this->month;
    }

    public function getDay(): int
    {
        return $this->day;
    }
}
