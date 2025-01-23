<?php

declare(strict_types=1);

namespace ChronicleKeeper\Calendar\Domain\Entity;

use ChronicleKeeper\Calendar\Domain\Entity\Calendar\Month;
use ChronicleKeeper\Calendar\Domain\Entity\Calendar\MoonCycle;
use ChronicleKeeper\Calendar\Domain\Entity\Calendar\WeekConfiguration;
use ChronicleKeeper\Calendar\Domain\Exception\MonthNotExists;
use ChronicleKeeper\Calendar\Domain\Exception\YearHasNotASequentialListOfMonths;
use ChronicleKeeper\Calendar\Domain\Exception\YearIsNotStartingWithFirstMonth;

use function array_combine;
use function array_keys;
use function array_map;
use function array_reduce;
use function count;
use function range;
use function reset;
use function usort;

class Calendar
{
    /** @var array<int, Month> $months */
    private array $months = [];

    private WeekConfiguration $weekConfiguration;
    private MoonCycle $moonCycle;

    public function setMonths(Month ...$months): void
    {
        if ($months === []) {
            return;
        }

        usort($months, static fn (Month $a, Month $b) => $a->indexInYear <=> $b->indexInYear);

        $this->months = array_combine(
            array_map(static fn (Month $month) => $month->indexInYear, $months),
            $months,
        );

        // Check if the numeric indexes of the months are valid
        $firstMonth = reset($months);
        if ($firstMonth->indexInYear !== 1) {
            throw new YearIsNotStartingWithFirstMonth();
        }

        // Check if the numeric indexes of the months are a sequence from lowest to highest
        if (array_keys($this->months) !== range(1, count($this->months))) {
            throw new YearHasNotASequentialListOfMonths();
        }
    }

    public function setWeekConfiguration(WeekConfiguration $weekConfiguration): void
    {
        if (isset($this->weekConfiguration)) {
            return;
        }

        $this->weekConfiguration = $weekConfiguration;
    }

    public function getWeeks(): WeekConfiguration
    {
        return $this->weekConfiguration;
    }

    public function setMoonCycle(MoonCycle $moonCycle): void
    {
        if (isset($this->moonCycle)) {
            return;
        }

        $this->moonCycle = $moonCycle;
    }

    public function getMoonCycle(): MoonCycle
    {
        return $this->moonCycle;
    }

    /** @return Month[] */
    public function getMonths(): array
    {
        return $this->months;
    }

    public function getMonthOfTheYear(int $index): Month
    {
        return $this->months[$index] ?? throw new MonthNotExists($index);
    }

    public function countDaysInYear(int $year): int
    {
        return array_reduce(
            $this->months,
            static fn (int $carry, Month $month) => $carry + $month->days->countInYear($year),
            0,
        );
    }

    public function countDaysInMonth(int $year, int $month): int
    {
        return $this->getMonthOfTheYear($month)->days->countInYear($year);
    }
}
