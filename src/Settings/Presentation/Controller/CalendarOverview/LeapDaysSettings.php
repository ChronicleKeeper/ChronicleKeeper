<?php

declare(strict_types=1);

namespace ChronicleKeeper\Settings\Presentation\Controller\CalendarOverview;

use ChronicleKeeper\Settings\Application\SettingsHandler;
use ChronicleKeeper\Settings\Domain\ValueObject\Settings\CalendarSettings;
use ChronicleKeeper\Settings\Domain\ValueObject\Settings\CalendarSettings\MonthSettings;
use ChronicleKeeper\Settings\Presentation\Form\Calendar\LeapDaysType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/** @phpstan-import-type MonthSettingsArray from MonthSettings */
#[Route('/settings/calendar_overview/leap_days/{month}', name: 'settings_calendar_leap_days', priority: 10)]
final class LeapDaysSettings extends AbstractController
{
    public function __construct(private readonly SettingsHandler $settingsHandler)
    {
    }

    public function __invoke(Request $request, int $month): Response
    {
        $settings            = $this->settingsHandler->get();
        $calendarSettings    = $settings->getCalendarSettings();
        $allCalendarSettings = $calendarSettings->toArray();
        $monthIndex          = $this->getMonthIndexFromArray($allCalendarSettings['months'], $month);
        $month               = $allCalendarSettings['months'][$monthIndex];

        $form = $this->createForm(LeapDaysType::class, $month);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $epochs                                     = $form->getData();
            $allCalendarSettings['months'][$monthIndex] = $epochs;

            $settings->setCalendarSettings(CalendarSettings::fromArray($allCalendarSettings));

            $this->settingsHandler->store();

            return $this->redirectToRoute('settings_calendar_overview');
        }

        return $this->render(
            'settings/calendar_overview/leapDays_settings.html.twig',
            ['form' => $form->createView(), 'month' => $month],
        );
    }

    /** @param array<MonthSettingsArray> $months */
    private function getMonthIndexFromArray(array $months, int $index): int
    {
        foreach ($months as $key => $month) {
            if ($month['index'] === $index) {
                return $key;
            }
        }

        throw $this->createNotFoundException('Month not found');
    }
}
