<?php

declare(strict_types=1);

namespace ChronicleKeeper\Calendar\Presentation\Controller;

use ChronicleKeeper\Calendar\Application\Query\GetCurrentDate;
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
        $today    = $this->queryService->query(new GetCurrentDate());

        if ($year === null || $month === null) {
            return $this->render(
                'calendar/calendar.html.twig',
                [
                    'calendar' => $calendar,
                    'viewDate' => clone $today,
                    'currentDate' => $today,
                ],
            );
        }

        if ($year < $calendar->getConfiguration()->beginsInYear) {
            return $this->redirectToRoute(
                'calendar',
                ['year' => $calendar->getConfiguration()->beginsInYear, 'month' => 1],
            );
        }

        // Handle month overflow/underflow
        if ($month < 1) {
            $year--;

            if ($year < $calendar->getConfiguration()->beginsInYear) {
                return $this->redirectToRoute(
                    'calendar',
                    ['year' => $calendar->getConfiguration()->beginsInYear, 'month' => 1],
                );
            }

            $month = count($calendar->getMonths());

            return $this->redirectToRoute(
                'calendar',
                ['year' => $year, 'month' => $month],
            );
        }

        if ($month > count($calendar->getMonths())) {
            $year++;
            $month = 1;

            return $this->redirectToRoute(
                'calendar',
                ['year' => $year, 'month' => $month],
            );
        }

        return $this->render(
            'calendar/calendar.html.twig',
            [
                'calendar' => $calendar,
                'viewDate' => (new CalendarDate($calendar, $year, $month, 1))->getFirstDayOfMonth(),
                'currentDate' => $today,
            ],
        );
    }
}
