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
 */
class LeapDaySettings implements JsonSerializable
{
    public function __construct(
        private readonly int $day,
        private readonly string $name,
        private readonly int|null $yearInterval = null,
    ) {
    }

    /** @param LeapDaySettingsArray $array */
    public static function fromArray(array $array): self
    {
        return new self(
            $array['day'],
            $array['name'],
            $array['year_interval'] ?? null,
        );
    }

    /** @return LeapDaySettingsArray */
    public function toArray(): array
    {
        $result = [
            'day' => $this->day,
            'name' => $this->name,
        ];

        if ($this->yearInterval !== null) {
            $result['year_interval'] = $this->yearInterval;
        }

        return $result;
    }

    /** @return LeapDaySettingsArray */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    public function getDay(): int
    {
        return $this->day;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getYearInterval(): int|null
    {
        return $this->yearInterval;
    }
}
