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
    public CalendarDate $viewDate;

    public function getFirstRegularDay(CalendarDate $date): CalendarDate|null
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

    public function isInCurrentMonth(CalendarDate $date): bool
    {
        return $date->getMonth() === $this->viewDate->getMonth();
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
        $currentMonth = $this->calendar->getMonthOfTheYear($this->viewDate->getMonth());

        return $currentMonth->days->getLeapDaysInYear($this->viewDate->getYear());
    }

    public function isLeapDayActive(LeapDay $leapDay): bool
    {
        $leadDayCalendarDate = new CalendarDate(
            $this->calendar,
            $this->viewDate->getYear(),
            $this->viewDate->getMonth(),
            $leapDay->dayOfTheMonth,
        );

        return $this->viewDate->isSame($leadDayCalendarDate);
    }

    public function getPreviousDayOfLeapDay(LeapDay $leapDay): CalendarDate
    {
        $leadDayCalendarDate = new CalendarDate(
            $this->calendar,
            $this->viewDate->getYear(),
            $this->viewDate->getMonth(),
            $leapDay->dayOfTheMonth,
        );

        return $leadDayCalendarDate->subDays(1);
    }

    public function getNextDayOfLeapDay(LeapDay $leapDay): CalendarDate
    {
        $leadDayCalendarDate = new CalendarDate(
            $this->calendar,
            $this->viewDate->getYear(),
            $this->viewDate->getMonth(),
            $leapDay->dayOfTheMonth,
        );

        return $leadDayCalendarDate->addDays(1);
    }
}
