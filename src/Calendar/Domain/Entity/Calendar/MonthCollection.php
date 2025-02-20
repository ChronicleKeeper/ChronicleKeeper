<?php

declare(strict_types=1);

namespace ChronicleKeeper\Calendar\Domain\Entity\Calendar;

use ChronicleKeeper\Calendar\Domain\Exception\MonthNotExists;
use ChronicleKeeper\Calendar\Domain\Exception\YearHasNotASequentialListOfMonths;
use ChronicleKeeper\Calendar\Domain\Exception\YearIsNotStartingWithFirstMonth;
use Countable;

use function array_combine;
use function array_keys;
use function array_map;
use function array_reduce;
use function count;
use function range;
use function reset;
use function usort;

final class MonthCollection implements Countable
{
    /** @var array<int, Month> */
    private array $months;

    public function __construct(Month ...$months)
    {
        if ($months === []) {
            $this->months = [];

            return;
        }

        usort($months, static fn (Month $a, Month $b) => $a->indexInYear <=> $b->indexInYear);

        $this->months = array_combine(
            array_map(static fn (Month $month) => $month->indexInYear, $months),
            $months,
        );

        $this->validate();
    }

    private function validate(): void
    {
        if ($this->months === []) {
            return;
        }

        $firstMonth = reset($this->months);
        if ($firstMonth->indexInYear !== 1) {
            throw new YearIsNotStartingWithFirstMonth();
        }

        if (array_keys($this->months) !== range(1, count($this->months))) {
            throw new YearHasNotASequentialListOfMonths();
        }
    }

    public function get(int $index): Month
    {
        return $this->months[$index] ?? throw new MonthNotExists($index);
    }

    /** @return array<int, Month> */
    public function getAll(): array
    {
        return $this->months;
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

    public function count(): int
    {
        return count($this->months);
    }
}
