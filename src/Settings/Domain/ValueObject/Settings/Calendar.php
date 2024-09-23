<?php

declare(strict_types=1);

namespace DZunke\NovDoc\Settings\Domain\ValueObject\Settings;

/**
 * @phpstan-type CalendarSettings = array{
 *     current_date: string,
 *     calendar_description: string
 * }
 */
readonly class Calendar
{
    public function __construct(
        private string $currentDate = '26. Arthan des 1262. Zyklus',
        private string $calendarDescription = '',
    ) {
    }

    /** @param CalendarSettings $settings */
    public static function fromArray(array $settings): Calendar
    {
        return new Calendar($settings['current_date'], $settings['calendar_description']);
    }

    /** @return CalendarSettings */
    public function toArray(): array
    {
        return [
            'current_date' => $this->currentDate,
            'calendar_description' => $this->calendarDescription,
        ];
    }

    public function getCurrentDate(): string
    {
        return $this->currentDate;
    }

    public function getCalendarDescription(): string
    {
        return $this->calendarDescription;
    }
}
