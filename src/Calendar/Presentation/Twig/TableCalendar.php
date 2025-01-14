<?php

declare(strict_types=1);

namespace ChronicleKeeper\Calendar\Presentation\Twig;

use ChronicleKeeper\Calendar\Domain\Entity\Calendar;
use ChronicleKeeper\Calendar\Domain\Entity\Calendar\LeapDay;
use ChronicleKeeper\Calendar\Domain\Entity\CalendarDate;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent(name: 'Calendar:Table', template: 'calendar/table_calendar.html.twig')]
class TableCalendar
{
    public Calendar $calendar;
    public CalendarDate $currentDate;

    public function getFirstRegularDay(CalendarDate $date): CalendarDate
    {
        $date = $date->getFirstDayOfMonth()->getFirstDayOfWeek();
        if (! $date->isLeapDay()) {
            return $date;
        }

        do {
            $date = $date->subDays(1);
        } while ($date->isLeapDay() === true);

        return $date;
    }

    public function getNextRegularDay(CalendarDate $date): CalendarDate
    {
        do {
            $date = $date->addDays(1);
        } while ($date->isLeapDay() === true);

        return $date;
    }

    /** @return Calendar\LeapDay[] */
    public function getLeapDaysOfCurrentMonth(): array
    {
        $currentMonth = $this->calendar->getMonthOfTheYear($this->currentDate->getMonth());

        return $currentMonth->days->getLeapDays();
    }

    public function getPreviousDayOfLeapDay(LeapDay $leapDay): string
    {
        $leadDayCalendarDate = new CalendarDate(
            $this->calendar,
            $this->currentDate->getYear(),
            $this->currentDate->getMonth(),
            $leapDay->dayOfTheMonth,
        );

        return $leadDayCalendarDate->subDays(1)->format();
    }

    public function getNextDayOfLeapDay(LeapDay $leapDay): string
    {
        $leadDayCalendarDate = new CalendarDate(
            $this->calendar,
            $this->currentDate->getYear(),
            $this->currentDate->getMonth(),
            $leapDay->dayOfTheMonth,
        );

        return $leadDayCalendarDate->addDays(1)->format();
    }
}
