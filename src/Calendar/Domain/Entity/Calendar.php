<?php

declare(strict_types=1);

namespace ChronicleKeeper\Calendar\Domain\Entity;

use ChronicleKeeper\Calendar\Domain\Entity\Calendar\Configuration;
use ChronicleKeeper\Calendar\Domain\Entity\Calendar\EpochCollection;
use ChronicleKeeper\Calendar\Domain\Entity\Calendar\Month;
use ChronicleKeeper\Calendar\Domain\Entity\Calendar\MonthCollection;
use ChronicleKeeper\Calendar\Domain\Entity\Calendar\MoonCycle;
use ChronicleKeeper\Calendar\Domain\Entity\Calendar\WeekConfiguration;

class Calendar
{
    private MonthCollection $months;

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
        $this->months = new MonthCollection();
    }

    public function setMonths(MonthCollection $months): void
    {
        if (isset($this->months) && $this->months->count() > 0) {
            return;
        }

        $this->months = $months;
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

    public function getMonths(): MonthCollection
    {
        return $this->months;
    }

    public function getConfiguration(): Configuration
    {
        return $this->configuration;
    }

    public function getMonth(int $index): Month
    {
        return $this->months->get($index);
    }

    /** @return array{days: int, leapDays: int} */
    public function getDaysUpToYear(int $year): array
    {
        if (! isset($this->cachedDaysInYear[$year])) {
            $days     = 0;
            $leapDays = 0;
            for ($i = $this->configuration->beginsInYear; $i < $year; $i++) {
                $days     += $this->months->countDaysInYear($i);
                $leapDays += $this->months->countLeapDaysInYear($i);
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
}
