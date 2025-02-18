<?php

declare(strict_types=1);

namespace ChronicleKeeper\Calendar\Domain\Entity\Calendar;

use ChronicleKeeper\Calendar\Domain\Entity\CalendarDate;
use ChronicleKeeper\Calendar\Domain\Exception\InvalidWeekConfiguration;
use ChronicleKeeper\Calendar\Domain\Exception\YearIsInvalidInCalendar;
use ChronicleKeeper\Calendar\Domain\ValueObject\LeapDay;
use ChronicleKeeper\Calendar\Domain\ValueObject\WeekDay;

use function abs;
use function array_combine;
use function array_keys;
use function array_map;
use function count;
use function range;

readonly class WeekConfiguration
{
    /** @var array<int, WeekDay> */
    private array $weekDays;

    public function __construct(WeekDay ...$weekDays)
    {
        if (count($weekDays) === 0) {
            throw InvalidWeekConfiguration::emptyWeekDays();
        }

        $this->weekDays = array_combine(
            array_map(static fn (WeekDay $day) => $day->index, $weekDays),
            $weekDays,
        );

        if (array_keys($this->weekDays) !== range(1, count($weekDays))) {
            throw InvalidWeekConfiguration::weekDaysNotSequential();
        }
    }

    /** @return array<int, WeekDay> */
    public function getDays(): array
    {
        return $this->weekDays;
    }

    public function countDays(): int
    {
        return count($this->weekDays);
    }

    public function getFirstDayOfWeekByDate(CalendarDate $date): CalendarDate
    {
        $weekDayOfDate = $date->getWeekDay();
        if (! $weekDayOfDate instanceof WeekDay) {
            try {
                return $this->getFirstDayOfWeekByDate($date->subDays(1));
            } catch (YearIsInvalidInCalendar) {
                // Totally fine when we run out of the calendar by looking backwards, then we look forward
                return $this->getFirstDayOfWeekByDate($date->addDays(1));
            }
        }

        if ($weekDayOfDate->index === 1) {
            return $date;
        }

        $daysBackToTheFirstDayOfWeek = abs(1 - $weekDayOfDate->index);
        $firstWeekDay                = $date->subDays($daysBackToTheFirstDayOfWeek);

        // Subtract leap days if there were some in the week because they are skipped
        $leapDaysToSkipInWeek = $date->countLeapDaysBetween($firstWeekDay);
        $firstWeekDay         = $firstWeekDay->subDays($leapDaysToSkipInWeek);

        if ($firstWeekDay->getDay() instanceof LeapDay) {
            do {
                $firstWeekDay = $firstWeekDay->subDays(1);
            } while ($firstWeekDay->getDay() instanceof LeapDay);
        }

        return $firstWeekDay;
    }

    public function getLastDayOfWeekByDate(CalendarDate $date): CalendarDate
    {
        $weekDayOfDate = $date->getWeekDay();
        if (! $weekDayOfDate instanceof WeekDay) {
            return $this->getLastDayOfWeekByDate($date->addDays(1));
        }

        if ($weekDayOfDate->index === $this->countDays()) {
            return $date;
        }

        // Get the raw diff of days until the next last week day
        $daysUntilTheNextLastWeekDay = abs($this->countDays() - $weekDayOfDate->index);
        $lastWeekDay                 = $date->addDays($daysUntilTheNextLastWeekDay);

        // Add leap days to count if there were some in the week because they are skipped
        $leapDaysToSkipInWeek = $date->countLeapDaysBetween($lastWeekDay);

        $lastWeekDay = $lastWeekDay->addDays($leapDaysToSkipInWeek);

        if ($lastWeekDay->getDay() instanceof LeapDay) {
            do {
                $lastWeekDay = $lastWeekDay->addDays(1);
            } while ($lastWeekDay->getDay() instanceof LeapDay);
        }

        return $lastWeekDay;
    }
}
