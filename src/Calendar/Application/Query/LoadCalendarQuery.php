<?php

declare(strict_types=1);

namespace ChronicleKeeper\Calendar\Application\Query;

use ChronicleKeeper\Calendar\Application\Service\CalendarFactory;
use ChronicleKeeper\Calendar\Domain\Entity\Calendar;
use ChronicleKeeper\Settings\Application\SettingsHandler;
use ChronicleKeeper\Shared\Application\Query\Query;
use ChronicleKeeper\Shared\Application\Query\QueryParameters;

final class LoadCalendarQuery implements Query
{
    public function __construct(
        private readonly SettingsHandler $settingsHandler,
        private readonly CalendarFactory $calendarFactory,
    ) {
    }

    public function query(QueryParameters $parameters): Calendar
    {
        $settings = $this->settingsHandler->get();

        return $this->calendarFactory->createFromSettings($settings->getCalendarSettings());
    }
}
