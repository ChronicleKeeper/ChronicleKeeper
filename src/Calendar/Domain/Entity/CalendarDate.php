<?php

declare(strict_types=1);

namespace ChronicleKeeper\Calendar\Domain\Entity;

use ChronicleKeeper\Calendar\Domain\Entity\Calendar\Month;
use ChronicleKeeper\Calendar\Domain\Entity\Calendar\WeekDay;
use ChronicleKeeper\Calendar\Domain\Exception\DayNotExistsInMonth;
use ChronicleKeeper\Calendar\Domain\Exception\MonthNotExists;
use ChronicleKeeper\Calendar\Domain\Exception\YearIsInvalidInCalendar;

use function abs;
use function count;
use function sprintf;
use function trim;

class CalendarDate
{
    private readonly Month $currentMonth;

    public function __construct(
        private readonly Calendar $calendar,
        private readonly int $year,
        private readonly int $month,
        private readonly int $day,
    ) {
        if ($this->year < $this->calendar->getConfiguration()->beginsInYear) {
            throw YearIsInvalidInCalendar::forTooEarlyYear($this->year);
        }

        // Check if month exists in calendar, method throws exception if not exists
        $this->currentMonth = $this->calendar->getMonthOfTheYear($this->month);

        // Check if day is valid for the given month
        $maxDaysInMonth = $this->currentMonth->days->count();

        if ($this->day < 1 || $this->day > $maxDaysInMonth) {
            throw new DayNotExistsInMonth($this->day, $this->month);
        }
    }

    public function getYear(): int
    {
        return $this->year;
    }

    public function getMonth(): int
    {
        return $this->month;
    }

    public function getDay(): string
    {
        return $this->currentMonth->days->getDay($this->day)->getLabel();
    }

    public function isSame(CalendarDate $calendarDate): bool
    {
        return $this->year === $calendarDate->year
            && $this->month === $calendarDate->month
            && $this->day === $calendarDate->day;
    }

    public function format(): string
    {
        $currentMonth = $this->calendar->getMonthOfTheYear($this->month);

        return trim(sprintf(
            '%d. %s %d %s',
            $currentMonth->days->getDay($this->day)->getLabel(),
            $this->calendar->getMonthOfTheYear($this->month)->name,
            $this->year,
            $this->calendar->getEpochCollection()->getEpochForYear($this->year)->name,
        ));
    }

    public function addDays(int $days): CalendarDate
    {
        $maxDaysInMonth = $this->calendar->getMonthOfTheYear($this->month)->days->count();

        // We stay in same month, so all fine
        if ($this->day + $days <= $maxDaysInMonth) {
            return new CalendarDate($this->calendar, $this->year, $this->month, $this->day + $days);
        }

        // We need to move to next month
        try {
            $nextMonth                = $this->calendar->getMonthOfTheYear($this->month + 1);
            $daysLeftAfterMonthChange = $days - ($maxDaysInMonth - $this->day) - 1;

            $monthChangeDate = new CalendarDate($this->calendar, $this->year, $nextMonth->indexInYear, 1);

            return $monthChangeDate->addDays($daysLeftAfterMonthChange);
        } catch (MonthNotExists) {
            // There is no next month, so we have to progress a year and start with the first month
            $nextYear                 = $this->year + 1;
            $nextMonth                = $this->calendar->getMonthOfTheYear(1);
            $daysLeftAfterMonthChange = $days - ($maxDaysInMonth - $this->day) - 1;

            $yearStartDate = new CalendarDate($this->calendar, $nextYear, $nextMonth->indexInYear, 1);

            return $yearStartDate->addDays($daysLeftAfterMonthChange);
        }
    }

    public function subDays(int $days): CalendarDate
    {
        if ($this->day - $days > 0) {
            return new CalendarDate($this->calendar, $this->year, $this->month, $this->day - $days);
        }

        // We need to move to previous month
        try {
            $previousMonth            = $this->calendar->getMonthOfTheYear($this->month - 1);
            $daysLeftAfterMonthChange = $days - $this->day;

            $monthChangeDate = new CalendarDate(
                $this->calendar,
                $this->year,
                $previousMonth->indexInYear,
                $previousMonth->days->count(),
            );

            return $monthChangeDate->subDays($daysLeftAfterMonthChange);
        } catch (MonthNotExists) {
            // There is no previous month, so we have to go back a year and start with the last month
            $previousYear             = $this->year - 1;
            $previousMonth            = $this->calendar->getMonthOfTheYear(count($this->calendar->getMonths()));
            $daysLeftAfterMonthChange = $days - $this->day;

            $yearStartDate = new CalendarDate(
                $this->calendar,
                $previousYear,
                $previousMonth->indexInYear,
                $previousMonth->days->count(),
            );

            return $yearStartDate->subDays($daysLeftAfterMonthChange);
        }
    }

    public function diffInDays(CalendarDate $date): int
    {
        $thisTotalDays = $this->getTotalDaysFromCalendarStart();
        $dateTotalDays = $date->getTotalDaysFromCalendarStart();

        return abs($thisTotalDays - $dateTotalDays);
    }

    public function getWeekDay(): WeekDay
    {
        $weekDays  = $this->calendar->getWeeks()->getDays();
        $totalDays = $this->getTotalDaysFromCalendarStart();

        $index = $totalDays % count($weekDays);

        return $weekDays[$index];
    }

    public function getFirstDayOfWeek(): CalendarDate
    {
        return $this->calendar->getWeeks()->getFirstDayOfWeekByDate($this);
    }

    public function getFirstDayOfMonth(): CalendarDate
    {
        return new CalendarDate($this->calendar, $this->year, $this->month, 1);
    }

    public function getLastDayOfMonth(): CalendarDate
    {
        $maxDaysInMonth = $this->calendar->getMonthOfTheYear($this->month)->days->count();

        return new CalendarDate($this->calendar, $this->year, $this->month, $maxDaysInMonth);
    }

    public function getTotalDaysFromCalendarStart(): int
    {
        $totalDays = 0;

        // Add days from complete months in current year
        $totalDays += $this->calendar->getDaysUpToMonthInYear($this->year, $this->month - 1);

        // Add remaining days in current month
        $totalDays += $this->day;

        return $totalDays;
    }
}
