<?php

declare(strict_types=1);

namespace ChronicleKeeper\Calendar\Presentation\Twig;

use ChronicleKeeper\Calendar\Domain\Entity\Calendar;
use ChronicleKeeper\Calendar\Domain\Entity\CalendarDate;
use Generator;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

use function ceil;

#[AsTwigComponent(name: 'Calendar:Table', template: 'calendar/table_calendar.html.twig')]
class TableCalendar
{
    public Calendar $calendar;
    public CalendarDate $currentDate;
    public CalendarDate $viewDate;

    public function createCalendarOfMonth(CalendarDate $date): Generator
    {
        $calendarStartsWith = $this->getFirstRegularDay($date);
        $lastDayOfTheMonth  = $date->getLastDayOfMonth();

        $totalAmountOfDaysToDisplay = $calendarStartsWith->diffInDays($lastDayOfTheMonth);
        $weeksToDisplay             = (int) ceil(
            $totalAmountOfDaysToDisplay / $this->calendar->getWeeks()->countDays(),
        );

        for ($weekIndex = 0; $weekIndex < $weeksToDisplay; $weekIndex++) {
            $week       = [];
            $daysInWeek = $this->calendar->getWeeks()->countDays();

            for ($dayIndex = 0; $dayIndex < $daysInWeek; $dayIndex++) {
                $week[]             = $calendarStartsWith;
                $calendarStartsWith = $this->getNextRegularDay($calendarStartsWith);
            }

            yield $week;
        }
    }

    public function getFirstRegularDay(CalendarDate $date): CalendarDate
    {
        return $date->getFirstDayOfMonth()->getFirstDayOfWeek();
    }

    public function isInCurrentMonth(CalendarDate $date): bool
    {
        return $date->getMonth() === $this->viewDate->getMonth();
    }

    public function getNextRegularDay(CalendarDate $date): CalendarDate
    {
        return $date->addDays(1);
    }
}
