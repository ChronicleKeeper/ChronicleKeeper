<?php

declare(strict_types=1);

namespace ChronicleKeeper\Calendar\Domain\Entity;

use ChronicleKeeper\Calendar\Domain\Entity\Calendar\Month;
use ChronicleKeeper\Calendar\Domain\Exception\MonthNotExists;

use function array_combine;
use function array_map;
use function array_reduce;
use function usort;

class Calendar
{
    /** @var array<int, Month> $months */
    private array $months = [];

    public function __construct()
    {
    }

    /** @param list<Month> $months */
    public function setMonths(array $months): void
    {
        usort($months, static fn (Month $a, Month $b) => $a->indexInYear <=> $b->indexInYear);

        $this->months = array_combine(
            array_map(static fn (Month $month) => $month->indexInYear, $months),
            $months,
        );
    }

    public function getMonthOfTheYear(int $index): Month
    {
        return $this->months[$index] ?? throw new MonthNotExists($index);
    }

    public function countDaysInYear(): int
    {
        return array_reduce(
            $this->months,
            static fn (int $carry, Month $month) => $carry + $month->numberOfDays,
            0,
        );
    }
}
