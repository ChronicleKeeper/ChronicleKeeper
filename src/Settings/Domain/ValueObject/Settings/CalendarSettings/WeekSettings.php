<?php

declare(strict_types=1);

namespace ChronicleKeeper\Settings\Domain\ValueObject\Settings\CalendarSettings;

use JsonSerializable;

/**
 * @phpstan-type WeekSettingsArray = array{
 *     index: int,
 *     name: string
 * }
 */
class WeekSettings implements JsonSerializable
{
    public function __construct(
        private readonly int $index,
        private readonly string $name,
    ) {
    }

    /** @param WeekSettingsArray $array */
    public static function fromArray(array $array): self
    {
        return new self(
            $array['index'],
            $array['name'],
        );
    }

    /** @return WeekSettingsArray */
    public function toArray(): array
    {
        return [
            'index' => $this->index,
            'name' => $this->name,
        ];
    }

    /** @return WeekSettingsArray */
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
}
