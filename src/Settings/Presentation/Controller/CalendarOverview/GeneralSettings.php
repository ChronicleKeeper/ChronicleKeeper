<?php

declare(strict_types=1);

namespace ChronicleKeeper\Settings\Presentation\Controller\CalendarOverview;

use ChronicleKeeper\Settings\Application\SettingsHandler;
use ChronicleKeeper\Settings\Domain\ValueObject\Settings\CalendarSettings;
use ChronicleKeeper\Settings\Presentation\Form\Calendar\GeneralType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/settings/calendar_overview/general', name: 'settings_calendar_general', priority: 10)]
final class GeneralSettings extends AbstractController
{
    public function __construct(private readonly SettingsHandler $settingsHandler)
    {
    }

    public function __invoke(Request $request): Response
    {
        $settings            = $this->settingsHandler->get();
        $calendarSettings    = $settings->getCalendarSettings();
        $allCalendarSettings = $calendarSettings->toArray();
        $moonsSettings       = $allCalendarSettings['moons'];

        $form     = $this->createForm(
            GeneralType::class,
            $data = [
                'moonName' => $moonsSettings[0]['moon_name'] ?? 'Mond',
                'moonCycleDays' => $moonsSettings[0]['moon_cycle_days'] ?? 30,
                'moonCycleOffset' => $moonsSettings[0]['moon_cycle_offset'] ?? 0,
                'current_day' => $allCalendarSettings['current_day'] ?? null,
            ],
        );

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();

            // Overwrite Settings
            $allCalendarSettings['moons'] = [
                [
                    'moon_name' => $data['moonName'],
                    'moon_cycle_days' => $data['moonCycleDays'],
                    'moon_cycle_offset' => $data['moonCycleOffset'],
                ],
            ];

            $allCalendarSettings['current_day'] = $data['current_day'];

            $settings->setCalendarSettings(CalendarSettings::fromArray($allCalendarSettings));

            $this->settingsHandler->store();

            return $this->redirectToRoute('settings_calendar_overview');
        }

        return $this->render(
            'settings/calendar_overview/general_settings.html.twig',
            ['form' => $form->createView(), 'data' => $data],
        );
    }
}
