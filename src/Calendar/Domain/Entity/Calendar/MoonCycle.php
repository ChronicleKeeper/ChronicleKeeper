<?php

declare(strict_types=1);

namespace ChronicleKeeper\Calendar\Domain\Entity\Calendar;

use ChronicleKeeper\Calendar\Domain\Entity\CalendarDate;
use ChronicleKeeper\Calendar\Domain\ValueObject\MoonState;
use Webmozart\Assert\Assert;

use function abs;
use function fmod;

class MoonCycle
{
    public function __construct(
        private readonly float $daysOfACycle,
    ) {
        Assert::greaterThan($daysOfACycle, 0, 'The moon cycle should have a days value.');
    }

    public function getMoonCycle(): float
    {
        return $this->daysOfACycle;
    }

    public function getDaysOfACycle(CalendarDate $date): float
    {
        $totalDays = $date->getTotalDaysFromCalendarStart();

        return fmod($totalDays, $this->daysOfACycle);
    }

    public function getMoonStateOfDay(CalendarDate $date): MoonState
    {
        $dayInCycle = $this->getDaysOfACycle($date);

        // Calculate the specific day for each major phase
        $newMoonDay      = 0;
        $firstQuarterDay = $this->daysOfACycle * 0.25;
        $fullMoonDay     = $this->daysOfACycle * 0.5;
        $lastQuarterDay  = $this->daysOfACycle * 0.75;

        // Define tolerance (half a day on each side)
        $tolerance = 0.5;

        // Check if we're within tolerance of a specific phase
        if ($this->isWithinTolerance($dayInCycle, $newMoonDay, $tolerance, $this->daysOfACycle)) {
            return MoonState::NEW_MOON;
        }

        if ($this->isWithinTolerance($dayInCycle, $firstQuarterDay, $tolerance)) {
            return MoonState::FIRST_QUARTER;
        }

        if ($this->isWithinTolerance($dayInCycle, $fullMoonDay, $tolerance)) {
            return MoonState::FULL_MOON;
        }

        if ($this->isWithinTolerance($dayInCycle, $lastQuarterDay, $tolerance)) {
            return MoonState::LAST_QUARTER;
        }

        // For days between major phases
        return match (true) {
            $dayInCycle < $firstQuarterDay => MoonState::WAXING_CRESCENT,
            $dayInCycle < $fullMoonDay => MoonState::WAXING_GIBBOUS,
            $dayInCycle < $lastQuarterDay => MoonState::WANING_GIBBOUS,
            default => MoonState::WANING_CRESCENT,
        };
    }

    /**
     * Check if a day is within tolerance of a target phase day
     */
    private function isWithinTolerance(
        float $day,
        float $targetDay,
        float $tolerance,
        float|null $cycleLength = null,
    ): bool {
        // Handle the wraparound case for new moon (day 0 = day cycleLength)
        if ($cycleLength !== null && ($targetDay === 0.0 || $targetDay === $cycleLength)) {
            return $day >= $cycleLength - $tolerance || $day < $tolerance;
        }

        return abs($day - $targetDay) < $tolerance;
    }
}
