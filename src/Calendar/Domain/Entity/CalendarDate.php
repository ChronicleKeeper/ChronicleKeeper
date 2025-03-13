<?php

declare(strict_types=1);

namespace ChronicleKeeper\Calendar\Domain\Entity;

use ChronicleKeeper\Calendar\Domain\Entity\Calendar\Day;
use ChronicleKeeper\Calendar\Domain\Entity\Calendar\Month;
use ChronicleKeeper\Calendar\Domain\Exception\DayNotExistsInMonth;
use ChronicleKeeper\Calendar\Domain\Exception\MonthNotExists;
use ChronicleKeeper\Calendar\Domain\Exception\YearIsInvalidInCalendar;
use ChronicleKeeper\Calendar\Domain\Service\DateFormatter;
use ChronicleKeeper\Calendar\Domain\ValueObject\LeapDay;
use ChronicleKeeper\Calendar\Domain\ValueObject\MoonState;
use ChronicleKeeper\Calendar\Domain\ValueObject\WeekDay;

use function abs;
use function count;

class CalendarDate
{
    private readonly Month $currentMonth;
    private readonly DateFormatter $formatter;
    private Day|null $dayInYear = null;

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
        $this->currentMonth = $this->calendar->getMonth($this->month);

        // Check if day is valid for the given month
        $maxDaysInMonthOfATheYear = $this->currentMonth->days->countInYear($this->year);

        if ($this->day < 1 || $this->day > $maxDaysInMonthOfATheYear) {
            throw DayNotExistsInMonth::forDayAtSpecificDate($this->year, $this->month, $this->day);
        }

        $this->formatter = new DateFormatter();
    }

    public function getYear(): int
    {
        return $this->year;
    }

    public function getMonth(): int
    {
        return $this->month;
    }

    public function getDay(): Day
    {
        return $this->dayInYear ?? ($this->dayInYear = $this->currentMonth->days->getDayInYear($this->day, $this->year));
    }

    public function getCalendar(): Calendar
    {
        return $this->calendar;
    }

    public function isSame(CalendarDate $calendarDate): bool
    {
        return $this->year === $calendarDate->year
            && $this->month === $calendarDate->month
            && $this->day === $calendarDate->day;
    }

    public function format(string|null $format = null): string
    {
        if ($this->getDay() instanceof LeapDay) {
            return $this->formatter->format($this, $format ?? '%d %Y');
        }

        return $this->formatter->format($this, $format ?? '%d. %M %Y');
    }

    public function addDays(int $days): CalendarDate
    {
        $maxDaysInMonth = $this->calendar->getMonth($this->month)->days->countInYear($this->year);

        // We stay in same month, so all fine
        if ($this->day + $days <= $maxDaysInMonth) {
            return new CalendarDate($this->calendar, $this->year, $this->month, $this->day + $days);
        }

        // We need to move to next month
        try {
            $nextMonth                = $this->calendar->getMonth($this->month + 1);
            $daysLeftAfterMonthChange = $days - ($maxDaysInMonth - $this->day) - 1;

            $monthChangeDate = new CalendarDate($this->calendar, $this->year, $nextMonth->indexInYear, 1);

            return $monthChangeDate->addDays($daysLeftAfterMonthChange);
        } catch (MonthNotExists) {
            // There is no next month, so we have to progress a year and start with the first month
            $nextYear                 = $this->year + 1;
            $nextMonth                = $this->calendar->getMonth(1);
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
            $previousMonth            = $this->calendar->getMonth($this->month - 1);
            $daysLeftAfterMonthChange = $days - $this->day;

            $monthChangeDate = new CalendarDate(
                $this->calendar,
                $this->year,
                $previousMonth->indexInYear,
                $previousMonth->days->countInYear($this->year),
            );

            return $monthChangeDate->subDays($daysLeftAfterMonthChange);
        } catch (MonthNotExists) {
            // There is no previous month, so we have to go back a year and start with the last month
            $previousYear             = $this->year - 1;
            $previousMonth            = $this->calendar->getMonth($this->calendar->getMonths()->count());
            $daysLeftAfterMonthChange = $days - $this->day;

            $yearStartDate = new CalendarDate(
                $this->calendar,
                $previousYear,
                $previousMonth->indexInYear,
                $previousMonth->days->countInYear($previousYear),
            );

            return $yearStartDate->subDays($daysLeftAfterMonthChange);
        }
    }

    public function diffInDays(CalendarDate $date, bool $withoutLeapDays = false): int
    {
        $thisTotalDays = $this->getTotalDaysFromCalendarStart($withoutLeapDays);
        $dateTotalDays = $date->getTotalDaysFromCalendarStart($withoutLeapDays);

        return abs($thisTotalDays - $dateTotalDays);
    }

    public function countLeapDaysBetween(CalendarDate $date): int
    {
        $daysDiffIncludingLeapDays = $this->diffInDays($date);
        $daysDiffExcludingLeapDays = $this->diffInDays($date, true);

        return abs($daysDiffIncludingLeapDays - $daysDiffExcludingLeapDays);
    }

    public function getWeekDay(): WeekDay|null
    {
        if ($this->getDay() instanceof LeapDay) {
            // Leap days do not have a week day
            return null;
        }

        $weekDays  = $this->calendar->getWeeks()->getDays();
        $totalDays = $this->getTotalDaysFromCalendarStart(true);
        $index     = ($totalDays - 1) % count($weekDays) + 1;

        return $weekDays[$index];
    }

    public function getFirstDayOfWeek(): CalendarDate
    {
        return $this->calendar->getWeeks()->getFirstDayOfWeekByDate($this);
    }

    public function getLastDayOfWeek(): CalendarDate
    {
        return $this->calendar->getWeeks()->getLastDayOfWeekByDate($this);
    }

    public function getMoonState(): MoonState
    {
        return $this->calendar->getMoonCycle()->getMoonStateOfDay($this);
    }

    public function getFirstDayOfMonth(): CalendarDate
    {
        return new CalendarDate($this->calendar, $this->year, $this->month, 1);
    }

    public function getLastDayOfMonth(): CalendarDate
    {
        $maxDaysInMonth = $this->calendar->getMonth($this->month)->days->countInYear($this->year);

        return new CalendarDate($this->calendar, $this->year, $this->month, $maxDaysInMonth);
    }

    public function getTotalDaysFromCalendarStart(bool $withoutLeapDays = false): int
    {
        $totalDays = 0;

        $countsUpToThisYear = $this->calendar->getDaysUpToMonthInYear($this->year, $this->month);

        // Add days from complete months in current year
        $totalDays += $countsUpToThisYear['days'];

        // Remove leap days if we do not want to have them
        if ($withoutLeapDays === true) {
            $totalDays -= $countsUpToThisYear['leapDays'];
        }

        // Add remaining days in current month
        $totalDays += $this->day;

        // Remove leap dys that were before this day if we want to exclude them
        if ($withoutLeapDays === true) {
            $totalDays -= $this->currentMonth->days->countLeapDaysUpToDayInYear($this->day, $this->year);
        }

        return $totalDays;
    }
}
