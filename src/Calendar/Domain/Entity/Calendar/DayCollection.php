<?php

declare(strict_types=1);

namespace ChronicleKeeper\Calendar\Domain\Entity\Calendar;

use Countable;

use function count;

final class DayCollection implements Countable
{
    /** @var array<int, Day> */
    private array $daysInTheMonth = [];

    /** @param int<0, max> $amountOfRegularDays */
    public function __construct(
        private readonly int $amountOfRegularDays,
    ) {
        $this->calculateDaysInTheMonth();
    }

    private function calculateDaysInTheMonth(): void
    {
        $dayOfTheMonthToCheck       = 1;
        $numericRegularDayToDisplay = 1;
        do {
            $this->daysInTheMonth[$dayOfTheMonthToCheck] = new RegularDay(
                $dayOfTheMonthToCheck,
                $numericRegularDayToDisplay,
            );

            ++$dayOfTheMonthToCheck;
            ++$numericRegularDayToDisplay;
        } while (count($this->daysInTheMonth) < $this->count());
    }

    public function getDay(int $day): Day
    {
        return $this->daysInTheMonth[$day];
    }

    public function countInYear(int $year): int
    {
        return $this->amountOfRegularDays;
    }

    public function count(): int
    {
        return $this->amountOfRegularDays;
    }

    public function getRegularDaysCount(): int
    {
        return $this->amountOfRegularDays;
    }
}
