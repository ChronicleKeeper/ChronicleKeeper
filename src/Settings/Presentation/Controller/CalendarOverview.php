<?php

declare(strict_types=1);

namespace ChronicleKeeper\Settings\Presentation\Controller;

use ChronicleKeeper\Settings\Application\SettingsHandler;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/settings/calendar_overview', name: 'settings_calendar_overview', priority: 10)]
final class CalendarOverview extends AbstractController
{
    public function __construct(
        private readonly SettingsHandler $settingsHandler,
    ) {
    }

    public function __invoke(): Response
    {
        return $this->render(
            'settings/calendar_settings.html.twig',
            ['calendar_settings' => $this->settingsHandler->get()->getCalendarSettings()],
        );
    }
}
