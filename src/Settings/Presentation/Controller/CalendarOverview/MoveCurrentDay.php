<?php

declare(strict_types=1);

namespace ChronicleKeeper\Settings\Presentation\Controller\CalendarOverview;

use ChronicleKeeper\Calendar\Application\Query\GetCurrentDate;
use ChronicleKeeper\Calendar\Domain\Entity\CalendarDate;
use ChronicleKeeper\Settings\Application\SettingsHandler;
use ChronicleKeeper\Settings\Domain\ValueObject\Settings\CalendarSettings\CurrentDay;
use ChronicleKeeper\Shared\Application\Query\QueryService;
use ChronicleKeeper\Shared\Presentation\FlashMessages\Alert;
use ChronicleKeeper\Shared\Presentation\FlashMessages\HandleFlashMessages;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

use function assert;

final class MoveCurrentDay extends AbstractController
{
    use HandleFlashMessages;

    public function __construct(
        private readonly QueryService $queryService,
        private readonly SettingsHandler $settingsHandler,
    ) {
    }

    #[Route('/settings/calendar_overview/add_days/{days}', name: 'settings_calendar_add_days', priority: 10)]
    public function add(Request $request, int $days): Response
    {
        $currentDate = $this->queryService->query(new GetCurrentDate());
        assert($currentDate instanceof CalendarDate);

        $newCurrentDate   = $currentDate->addDays($days);
        $settings         = $this->settingsHandler->get();
        $calendarSettings = $settings->getCalendarSettings();

        $settings->setCalendarSettings($calendarSettings->withCurrentDay(new CurrentDay(
            $newCurrentDate->getYear(),
            $newCurrentDate->getMonth(),
            $newCurrentDate->getDay()->getDayOfTheMonth(),
        )));

        $this->settingsHandler->store();

        $this->addFlashMessage(
            $request,
            Alert::SUCCESS,
            'Dem aktuellen Datum wurden ' . $days . ' Tage hinzugefügt.',
        );

        return $this->redirectToRoute('calendar');
    }

    #[Route('/settings/calendar_overview/sub_days/{days}', name: 'settings_calendar_sub_days', priority: 10)]
    public function sub(Request $request, int $days): Response
    {
        $currentDate = $this->queryService->query(new GetCurrentDate());
        assert($currentDate instanceof CalendarDate);

        $newCurrentDate   = $currentDate->subDays($days);
        $settings         = $this->settingsHandler->get();
        $calendarSettings = $settings->getCalendarSettings();

        $settings->setCalendarSettings($calendarSettings->withCurrentDay(new CurrentDay(
            $newCurrentDate->getYear(),
            $newCurrentDate->getMonth(),
            $newCurrentDate->getDay()->getDayOfTheMonth(),
        )));

        $this->settingsHandler->store();

        $this->addFlashMessage(
            $request,
            Alert::SUCCESS,
            'Dem aktuellen Datum wurden ' . $days . ' Tage abgezogen.',
        );

        return $this->redirectToRoute('calendar');
    }
}
