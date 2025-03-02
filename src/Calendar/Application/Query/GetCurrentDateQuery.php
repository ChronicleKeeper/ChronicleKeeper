<?php

declare(strict_types=1);

namespace ChronicleKeeper\Calendar\Application\Query;

use ChronicleKeeper\Calendar\Domain\Entity\CalendarDate;
use ChronicleKeeper\Settings\Application\SettingsHandler;
use ChronicleKeeper\Settings\Domain\ValueObject\Settings\CalendarSettings\CurrentDay;
use ChronicleKeeper\Shared\Application\Query\Query;
use ChronicleKeeper\Shared\Application\Query\QueryParameters;
use ChronicleKeeper\Shared\Application\Query\QueryService;

use function assert;

final class GetCurrentDateQuery implements Query
{
    public function __construct(
        private readonly QueryService $queryService,
        private readonly SettingsHandler $settingsHandler,
    ) {
    }

    public function query(QueryParameters $parameters): CalendarDate
    {
        assert($parameters instanceof CurrentDay);

        $calendar = $this->queryService->query(new LoadCalendar());
        $settings = $this->settingsHandler->get();

        $currentDaySettings = $settings->getCalendarSettings()->getCurrentDay();
        if ($currentDaySettings instanceof CurrentDay) {
            return new CalendarDate(
                $calendar,
                $currentDaySettings->getYear(),
                $currentDaySettings->getMonth(),
                $currentDaySettings->getDay(),
            );
        }

        return new CalendarDate($calendar, 0, 1, 1);
    }
}
