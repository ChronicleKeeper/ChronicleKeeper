<?php

declare(strict_types=1);

namespace ChronicleKeeper\Calendar\Domain\Entity\Calendar;

use ChronicleKeeper\Calendar\Domain\Exception\InvalidLeapDays;
use Countable;

use function array_combine;
use function array_keys;
use function array_map;
use function count;
use function range;
use function usort;

readonly class DayCollection implements Countable
{
    /** @var array<int, LeapDay> */
    private array $leapDays;

    /** @param int<0, max> $maxRegularDays */
    public function __construct(
        private int $maxRegularDays,
        LeapDay ...$leapDays,
    ) {
        if ($leapDays === []) {
            $this->leapDays = [];

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
    }

    /** @return LeapDay[] */
    public function getLeapDays(): array
    {
        return $this->leapDays;
    }

    public function isLeapDay(int $day): bool
    {
        return isset($this->leapDays[$day]);
    }

    public function getLeapDay(int $day): LeapDay
    {
        if (! $this->isLeapDay($day)) {
            throw InvalidLeapDays::theLeapDayDoesNotExist($day);
        }

        return $this->leapDays[$day];
    }

    public function count(): int
    {
        return $this->maxRegularDays + count($this->leapDays);
    }

    public function getLeapDaysCount(): int
    {
        return count($this->leapDays);
    }
}
