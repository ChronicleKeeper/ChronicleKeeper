<?php

declare(strict_types=1);

namespace ChronicleKeeper\Calendar\Domain\Entity;

use ChronicleKeeper\Calendar\Domain\Entity\Calendar\WeekDay;
use ChronicleKeeper\Calendar\Domain\Exception\DayNotExistsInMonth;
use ChronicleKeeper\Calendar\Domain\Exception\MonthNotExists;

use function count;
use function sprintf;

class CalendarDate
{
    public function __construct(
        private readonly Calendar $calendar,
        private readonly int $year,
        private readonly int $month,
        private readonly int $day,
    ) {
        // Check if month exists in calendar, method throws exception if not exists
        $month = $this->calendar->getMonthOfTheYear($this->month);

        // Check if day is valid for the given month
        $maxDaysInMonth = $month->days->count();

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

    public function getDay(): int
    {
        return $this->day;
    }

    public function format(): string
    {
        $currentMonth = $this->calendar->getMonthOfTheYear($this->month);
        if ($currentMonth->days->isLeapDay($this->day)) {
            return sprintf(
                '%s %d',
                $currentMonth->days->getLeapDay($this->day)->name,
                $this->year,
            );
        }

        return sprintf(
            '%d. %s %d',
            $this->day,
            $this->calendar->getMonthOfTheYear($this->month)->name,
            $this->year,
        );
    }

    public function isLeapDay(): bool
    {
        return $this->calendar->getMonthOfTheYear($this->month)->days->isLeapDay($this->day);
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

            $monthChangeDate = new CalendarDate($this->calendar, $this->year, $previousMonth->indexInYear, $previousMonth->days->count());

            return $monthChangeDate->subDays($daysLeftAfterMonthChange);
        } catch (MonthNotExists) {
            // There is no previous month, so we have to go back a year and start with the last month
            $previousYear             = $this->year - 1;
            $previousMonth            = $this->calendar->getMonthOfTheYear(12);
            $daysLeftAfterMonthChange = $days - $this->day;

            $yearStartDate = new CalendarDate($this->calendar, $previousYear, $previousMonth->indexInYear, $previousMonth->days->count());

            return $yearStartDate->subDays($daysLeftAfterMonthChange);
        }
    }

    public function getWeekDay(): WeekDay
    {
        $weekDays  = $this->calendar->getWeeks()->getDays();
        $totalDays = $this->calculateTotalDaysUntilToday();
        $index     = ($totalDays - 1) % count($weekDays) + 1;

        return $weekDays[$index];
    }

    private function calculateTotalDaysUntilToday(): int
    {
        $totalDays = 0;

        // Add days from previous years
        $totalDays += ($this->getYear() - 1) * $this->calendar->countDaysInYear();

        // Extract the leap days of the last year
        // TODO: As we have leap days possible only in specific years this has to be based on the year!

        // Add days from previous months in current year
        for ($i = 1; $i < $this->getMonth(); $i++) {
            $totalDays += $this->calendar->getMonthOfTheYear($i)->days->count();
            // Substract the leap days of the month when they must not be counted
            // TODO: As we have leap days possible only in specific months this has to be based on the month!
        }

        // Add days in current month
        $totalDays += $this->getDay();

        return $totalDays;
    }
}
