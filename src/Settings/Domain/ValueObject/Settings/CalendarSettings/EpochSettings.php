<?php

declare(strict_types=1);

namespace ChronicleKeeper\Settings\Domain\ValueObject\Settings\CalendarSettings;

use JsonSerializable;

/**
 * @phpstan-type EpochSettingsArray = array{
 *     name: string,
 *     start_year: int<0, max>,
 *     end_year?: int<0, max>|null
 * }
 */
class EpochSettings implements JsonSerializable
{
    /**
     * @param int<0, max>      $startYear
     * @param int<0, max>|null $endYear
     */
    public function __construct(
        private readonly string $name,
        private readonly int $startYear,
        private readonly int|null $endYear = null,
    ) {
    }

    /** @param EpochSettingsArray $array */
    public static function fromArray(array $array): self
    {
        return new self(
            $array['name'],
            $array['start_year'],
            $array['end_year'] ?? null,
        );
    }

    /** @return EpochSettingsArray */
    public function toArray(): array
    {
        $result = [
            'name' => $this->name,
            'start_year' => $this->startYear,
        ];

        if ($this->endYear !== null) {
            $result['end_year'] = $this->endYear;
        }

        return $result;
    }

    /** @return EpochSettingsArray */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getStartYear(): int
    {
        return $this->startYear;
    }

    public function getEndYear(): int|null
    {
        return $this->endYear;
    }
}
