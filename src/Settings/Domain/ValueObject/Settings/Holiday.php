<?php

declare(strict_types=1);

namespace ChronicleKeeper\Settings\Domain\ValueObject\Settings;

/**
 * @phpstan-type HolidaySettings = array{
 *     description: string
 * }
 */
readonly class Holiday
{
    public function __construct(
        private string $description = '',
    ) {
    }

    /** @param HolidaySettings $settings */
    public static function fromArray(array $settings): Holiday
    {
        return new Holiday($settings['description']);
    }

    /** @return HolidaySettings */
    public function toArray(): array
    {
        return ['description' => $this->description];
    }

    public function getDescription(): string
    {
        return $this->description;
    }
}
