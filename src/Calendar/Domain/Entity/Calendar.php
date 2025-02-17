<?php

declare(strict_types=1);

namespace ChronicleKeeper\Calendar\Domain\Entity;

use ChronicleKeeper\Calendar\Domain\Entity\Calendar\Configuration;
use ChronicleKeeper\Calendar\Domain\Entity\Calendar\EpochCollection;
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
    /** @var array<int, Month> */
    private array $months = [];

    /** @var array<int, int> */
    private array $cachedDaysInYear = [];
    /** @var array<int, int> */
    private array $cachedLeapDaysInYear = [];

    /** @var array<int, array<int, int>> */
    private array $cachedDaysInMonth = [];
    /** @var array<int, array<int, int>> */
    private array $cachedLeapDaysInMonth = [];

    private EpochCollection $epochCollection;
    private WeekConfiguration $weekConfiguration;
    private MoonCycle $moonCycle;

    public function __construct(
        private readonly Configuration $configuration,
    ) {
    }

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

    public function setEpochCollection(EpochCollection $epochCollection): void
    {
        if (isset($this->epochCollection)) {
            return;
        }

        $this->epochCollection = $epochCollection;
    }

    public function getEpochCollection(): EpochCollection
    {
        return $this->epochCollection;
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

    public function getConfiguration(): Configuration
    {
        return $this->configuration;
    }

    public function getMonth(int $index): Month
    {
        return $this->months[$index] ?? throw new MonthNotExists($index);
    }

    /** @return array{days: int, leapDays: int} */
    public function getDaysUpToYear(int $year): array
    {
        if (! isset($this->cachedDaysInYear[$year])) {
            $days     = 0;
            $leapDays = 0;
            for ($i = $this->configuration->beginsInYear; $i < $year; $i++) {
                $days     += $this->countDaysInYear($i);
                $leapDays += $this->countLeapDaysInYear($i);
            }

            $this->cachedDaysInYear[$year]     = $days;
            $this->cachedLeapDaysInYear[$year] = $leapDays;
        }

        return [
            'days' => $this->cachedDaysInYear[$year],
            'leapDays' => $this->cachedLeapDaysInYear[$year],
        ];
    }

    /** @return array{days: int, leapDays: int} */
    public function getDaysUpToMonthInYear(int $year, int $month): array
    {
        if (! isset($this->cachedDaysInMonth[$year][$month])) {
            $daysFromYear = $this->getDaysUpToYear($year);

            $days     = $daysFromYear['days'];
            $leapDays = $daysFromYear['leapDays'];

            for ($m = 1; $m < $month; $m++) {
                $days     += $this->getMonth($m)->days->countInYear($year);
                $leapDays += $this->getMonth($m)->days->countLeapDaysInYear($year);
            }

            $this->cachedDaysInMonth[$year][$month]     = $days;
            $this->cachedLeapDaysInMonth[$year][$month] = $leapDays;
        }

        return [
            'days' => $this->cachedDaysInMonth[$year][$month],
            'leapDays' => $this->cachedLeapDaysInMonth[$year][$month],
        ];
    }

    public function countDaysInYear(int $year): int
    {
        return array_reduce(
            $this->months,
            static fn (int $carry, Month $month) => $carry + $month->days->countInYear($year),
            0,
        );
    }

    public function countLeapDaysInYear(int $year): int
    {
        return array_reduce(
            $this->months,
            static fn (int $carry, Month $month) => $carry + $month->days->countLeapDaysInYear($year),
            0,
        );
    }
}
