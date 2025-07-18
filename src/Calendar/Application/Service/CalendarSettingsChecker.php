<?php

declare(strict_types=1);

namespace ChronicleKeeper\Calendar\Application\Service;

use ChronicleKeeper\Calendar\Application\Exception\CalendarConfigurationIncomplete;
use ChronicleKeeper\Settings\Application\SettingsHandler;
use ChronicleKeeper\Settings\Domain\ValueObject\Settings\CalendarSettings\CurrentDay;

class CalendarSettingsChecker
{
    public function __construct(
        private readonly SettingsHandler $settingsHandler,
    ) {
    }

    public function hasValidSettings(bool $throw = true): bool
    {
        $calendarSettings = $this->settingsHandler->get()->getCalendarSettings();
        $missingSettings  = [];

        if (! $calendarSettings->getCurrentDay() instanceof CurrentDay) {
            $missingSettings[] = 'Das Aktuelles Datum (Heute) fehlt.';
        }

        if ($calendarSettings->getEpochs() === []) {
            $missingSettings[] = 'Mindestens eine Epochen (Ära) erforderlich.';
        }

        if ($calendarSettings->getMonths() === []) {
            $missingSettings[] = 'Mindestens ein Monat erforderlich.';
        }

        if ($calendarSettings->getWeeks() === []) {
            $missingSettings[] = 'Es sollte mindestens einen Wochentag geben.';
        }

        if ($missingSettings === []) {
            return true;
        }

        if ($throw === true) {
            throw new CalendarConfigurationIncomplete($missingSettings);
        }

        return false;
    }
}
