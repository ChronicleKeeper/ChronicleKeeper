<?php

declare(strict_types=1);

namespace DZunke\NovDoc\Domain\Settings\Settings;

/**
 * @phpstan-type MoonCalendarSettings = array{
 *     calendar_description: string,
 * }
 */
readonly class MoonCalendar
{
    public function __construct(
        private string $moonCalendarDescription = '',
    ) {
    }

    /** @param MoonCalendarSettings $settings */
    public static function fromArray(array $settings): MoonCalendar
    {
        return new MoonCalendar($settings['calendar_description']);
    }

    /** @return MoonCalendarSettings */
    public function toArray(): array
    {
        return [
            'calendar_description' => $this->moonCalendarDescription,
        ];
    }

    public function getMoonCalendarDescription(): string
    {
        return $this->moonCalendarDescription;
    }
}
