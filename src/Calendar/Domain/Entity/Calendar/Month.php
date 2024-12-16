<?php

declare(strict_types=1);

namespace ChronicleKeeper\Calendar\Domain\Entity\Calendar;

use ChronicleKeeper\Calendar\Domain\Entity\Calendar;
use ChronicleKeeper\Calendar\Domain\Exception\InvalidLeapDays;

use function array_combine;
use function array_keys;
use function array_map;
use function count;
use function range;
use function usort;

class Month
{
    /** @var array<int, LeapDay> */
    private array $leapDays = [];

    public function __construct(
        public readonly Calendar $calendar,
        public readonly int $indexInYear,
        public readonly string $name,
        private readonly int $numberOfDays, // For leap days this then has to be in a special method
    ) {
    }

    public function setLeapDays(LeapDay ...$leapDays): void
    {
        // Leap days are just read only after they are initially set
        if ($this->leapDays !== []) {
            throw InvalidLeapDays::leapDaysAreAlreadySet($this->indexInYear);
        }

        usort($leapDays, static fn (LeapDay $a, LeapDay $b) => $a->dayOfTheMonth <=> $b->dayOfTheMonth);

        $this->leapDays = array_combine(
            array_map(static fn (LeapDay $month) => $month->dayOfTheMonth, $leapDays),
            $leapDays,
        );

        // Validate the amount of leap days equals the input
        if (count($this->leapDays) !== count($leapDays)) {
            throw InvalidLeapDays::leapDaysAreNotUnique();
        }

        // validate leap days are sequence of the month from the max days of the months on
        $maxDaysInMonth = $this->numberOfDays;
        if (array_keys($this->leapDays) !== range($maxDaysInMonth + 1, $maxDaysInMonth + count($this->leapDays))) {
            throw InvalidLeapDays::leapDaysAreNotSequence();
        }
    }

    public function isLeapDay(int $day): bool
    {
        return isset($this->leapDays[$day]);
    }

    public function getLeapDay(int $day): LeapDay
    {
        if (! $this->isLeapDay($day)) {
            throw InvalidLeapDays::theLeapDayDoesNotExist($day, $this->indexInYear);
        }

        return $this->leapDays[$day];
    }

    public function getDayCount(): int
    {
        return $this->numberOfDays + count($this->leapDays);
    }

    public function getLeapDaysCount(): int
    {
        return count($this->leapDays);
    }
}
