<?php

declare(strict_types=1);

namespace ChronicleKeeper\Calendar\Presentation\Controller;

use ChronicleKeeper\Calendar\Application\Query\LoadCalendar;
use ChronicleKeeper\Calendar\Domain\Entity\CalendarDate;
use ChronicleKeeper\Shared\Application\Query\QueryService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

use function count;

final class Calendar extends AbstractController
{
    public function __construct(
        private readonly QueryService $queryService,
    ) {
    }

    #[Route(
        '/calendar/{year}/{month}',
        name: 'calendar',
        defaults: ['year' => null, 'month' => null],
    )]
    public function __invoke(int|null $year = null, int|null $month = null): Response
    {
        $calendar = $this->queryService->query(new LoadCalendar());
        $today    = new CalendarDate($calendar, 2, 1, 14); // Fixed current day

        if ($year === null || $month === null) {
            $viewDate = clone $today;
        } else {
            // Handle month overflow/underflow
            if ($month < 1) {
                $year--;

                if ($year < 0) {
                    return $this->redirectToRoute('calendar', ['year' => 0, 'month' => 1]);
                }

                $month = count($calendar->getMonths());

            } elseif ($month > count($calendar->getMonths())) {
                $year++;
                $month = 1;
            }

            $viewDate = new CalendarDate($calendar, $year, $month, 1);
        }

        return $this->render(
            'calendar/calendar.html.twig',
            [
                'calendar' => $calendar,
                'viewDate' => $viewDate,
                'currentDate' => $today,
            ],
        );
    }
}
