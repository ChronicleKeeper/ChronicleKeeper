<?php

declare(strict_types=1);

namespace ChronicleKeeper\Calendar\Presentation\Controller;

use ChronicleKeeper\Calendar\Application\Query\LoadCalendar;
use ChronicleKeeper\Calendar\Domain\Entity\CalendarDate;
use ChronicleKeeper\Shared\Application\Query\QueryService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class Calendar extends AbstractController
{
    public function __construct(
        private readonly QueryService $queryService,
    ) {
    }

    #[Route('/calendar', name: 'calendar')]
    public function __invoke(): Response
    {
        $calendar    = $this->queryService->query(new LoadCalendar());
        $currentDate = new CalendarDate($calendar, 1265, 1, 12);

        return $this->render(
            'calendar/calendar.html.twig',
            ['calendar' => $calendar, 'currentDate' => $currentDate],
        );
    }
}
