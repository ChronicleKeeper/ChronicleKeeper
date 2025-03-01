<?php

declare(strict_types=1);

namespace ChronicleKeeper\Settings\Presentation\Controller\CalendarOverview;

use ChronicleKeeper\Settings\Application\SettingsHandler;
use ChronicleKeeper\Settings\Domain\ValueObject\Settings\CalendarSettings;
use ChronicleKeeper\Settings\Presentation\Form\Calendar\MonthsType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/settings/calendar_overview/months', name: 'settings_calendar_months', priority: 10)]
final class MonthsSettings extends AbstractController
{
    public function __construct(private readonly SettingsHandler $settingsHandler)
    {
    }

    public function __invoke(Request $request): Response
    {
        $settings            = $this->settingsHandler->get();
        $calendarSettings    = $settings->getCalendarSettings();
        $allCalendarSettings = $calendarSettings->toArray();
        $form                = $this->createForm(MonthsType::class, ['months' => $allCalendarSettings['months']]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $epochs                        = $form->getData();
            $allCalendarSettings['months'] = $epochs['months'];

            $settings->setCalendarSettings(CalendarSettings::fromArray($allCalendarSettings));

            $this->settingsHandler->store();

            return $this->redirectToRoute('settings_calendar_overview');
        }

        return $this->render(
            'settings/calendar_overview/months_settings.html.twig',
            ['form' => $form->createView(), 'months' => ['months' => $allCalendarSettings['months']]],
        );
    }
}
