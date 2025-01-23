<?php

declare(strict_types=1);

namespace ChronicleKeeper\Calendar\Domain\Entity\Calendar;

use ChronicleKeeper\Calendar\Domain\Exception\InvalidLeapDays;
use Countable;

use function array_combine;
use function array_filter;
use function array_map;
use function count;
use function usort;

final class DayCollection implements Countable
{
    /** @var array<int, Day> */
    private array $daysInTheMonth = [];

    /** @var array<int, LeapDay> */
    private array $leapDays;

    /** @param int<0, max> $amountOfRegularDays */
    public function __construct(
        private readonly int $amountOfRegularDays,
        LeapDay ...$leapDays,
    ) {
        if ($leapDays === []) {
            $this->leapDays = [];
            $this->calculateDaysInTheMonth();

            return;
        }

        usort($leapDays, static fn (LeapDay $a, LeapDay $b) => $a->dayOfTheMonth <=> $b->dayOfTheMonth);

        $this->leapDays = array_combine(
            array_map(static fn (LeapDay $leapDay) => $leapDay->dayOfTheMonth, $leapDays),
            $leapDays,
        );

        // Validate the amount of leap days equals the input
        if (count($this->leapDays) !== count($leapDays)) {
            throw InvalidLeapDays::leapDaysAreNotUnique();
        }

        $this->calculateDaysInTheMonth();
    }

    private function calculateDaysInTheMonth(): void
    {
        $dayOfTheMonthToCheck       = 1;
        $numericRegularDayToDisplay = 1;
        do {
            $leapDay = $this->leapDays[$dayOfTheMonthToCheck] ?? false;

            if ($leapDay instanceof LeapDay) {
                $this->daysInTheMonth[$dayOfTheMonthToCheck] = $leapDay;
                ++$dayOfTheMonthToCheck;

                continue;
            }

            $this->daysInTheMonth[$dayOfTheMonthToCheck] = new RegularDay(
                $dayOfTheMonthToCheck,
                $numericRegularDayToDisplay,
            );

            ++$dayOfTheMonthToCheck;
            ++$numericRegularDayToDisplay;
        } while (count($this->daysInTheMonth) < $this->count());
    }

    /** @return LeapDay[] */
    public function getLeapDays(): array
    {
        return $this->leapDays;
    }

    /** @return LeapDay[] */
    public function getLeapDaysInYear(int $year): array
    {
        return array_filter(
            $this->leapDays,
            static fn (LeapDay $leapDay) => $leapDay->occursInYear($year),
        );
    }

    public function getDay(int $day): Day
    {
        return $this->daysInTheMonth[$day];
    }

    public function isLeapDay(int $day): bool
    {
        return isset($this->daysInTheMonth[$day]) && $this->daysInTheMonth[$day] instanceof LeapDay;
    }

    public function countInYear(int $year): int
    {
        return $this->amountOfRegularDays + count($this->getLeapDaysInYear($year));
    }

    public function count(): int
    {
        return $this->amountOfRegularDays + count($this->leapDays);
    }

    public function getRegularDaysCount(): int
    {
        return $this->amountOfRegularDays;
    }

    public function getLeapDaysCount(): int
    {
        return count($this->leapDays);
    }

    public function countLeapDaysInYear(int $year): int
    {
        return count($this->getLeapDaysInYear($year));
    }
}
