<?php

declare(strict_types=1);

namespace ChronicleKeeper\Calendar\Presentation\Twig;

use ChronicleKeeper\Calendar\Domain\Entity\Calendar;
use ChronicleKeeper\Calendar\Domain\Entity\CalendarDate;
use ChronicleKeeper\Calendar\Domain\ValueObject\LeapDay;
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
        $lastDayOfTheMonth  = $this->getLasRegularDay($date);

        $totalAmountOfDaysToDisplay = $calendarStartsWith->diffInDays($lastDayOfTheMonth, true);
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

    public function getLasRegularDay(CalendarDate $date): CalendarDate
    {
        return $date->getLastDayOfMonth()->getLastDayOfWeek();
    }

    public function isInCurrentMonth(CalendarDate $date): bool
    {
        return $date->getMonth() === $this->viewDate->getMonth();
    }

    public function getNextRegularDay(CalendarDate $date): CalendarDate
    {
        do {
            $date = $date->addDays(1);
        } while ($date->getDay() instanceof LeapDay);

        return $date;
    }
}
