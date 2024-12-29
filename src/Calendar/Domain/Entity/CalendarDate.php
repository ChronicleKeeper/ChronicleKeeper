<?php

declare(strict_types=1);

namespace ChronicleKeeper\Calendar\Domain\Entity;

use ChronicleKeeper\Calendar\Domain\Exception\DayNotExistsInMonth;
use ChronicleKeeper\Calendar\Domain\Exception\MonthNotExists;

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
}
