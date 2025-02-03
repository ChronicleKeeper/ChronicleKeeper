<?php

declare(strict_types=1);

namespace ChronicleKeeper\Calendar\Domain\Entity\Calendar;

use ChronicleKeeper\Calendar\Domain\Entity\CalendarDate;
use ChronicleKeeper\Calendar\Domain\Exception\InvalidWeekConfiguration;

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

        if ($weekDayOfDate->index === 1) {
            return $date;
        }

        $daysBackToTheFirstDayOfWeek = abs(1 - $weekDayOfDate->index);

        return $date->subDays($daysBackToTheFirstDayOfWeek);
    }
}
