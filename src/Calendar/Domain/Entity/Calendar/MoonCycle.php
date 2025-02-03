<?php

declare(strict_types=1);

namespace ChronicleKeeper\Calendar\Domain\Entity\Calendar;

use ChronicleKeeper\Calendar\Domain\Entity\CalendarDate;
use ChronicleKeeper\Calendar\Domain\ValueObject\MoonState;
use Webmozart\Assert\Assert;

use function fmod;

class MoonCycle
{
    public function __construct(
        private readonly float $daysOfACycle,
    ) {
        Assert::greaterThan($daysOfACycle, 0, 'The moon cycle should have a days value.');
    }

    public function getMoonStateOfDay(CalendarDate $date): MoonState
    {
        $totalDays  = $date->getTotalDaysFromCalendarStart();
        $dayInCycle = fmod($totalDays, $this->daysOfACycle);

        // Normalize to 0-1 range (complete cycle)
        $normalizedAge = $dayInCycle / $this->daysOfACycle;

        // Phase boundaries based on normalized age
        return match (true) {
            $normalizedAge < 0.067 => MoonState::NEW_MOON,           // 0-6.7%
            $normalizedAge < 0.217 => MoonState::WAXING_CRESCENT,    // 6.7-21.7%
            $normalizedAge < 0.283 => MoonState::FIRST_QUARTER,      // 21.7-28.3%
            $normalizedAge < 0.467 => MoonState::WAXING_GIBBOUS,     // 28.3-46.7%
            $normalizedAge < 0.533 => MoonState::FULL_MOON,          // 46.7-53.3%
            $normalizedAge < 0.717 => MoonState::WANING_GIBBOUS,     // 53.3-71.7%
            $normalizedAge < 0.783 => MoonState::LAST_QUARTER,       // 71.7-78.3%
            $normalizedAge < 0.933 => MoonState::WANING_CRESCENT,    // 78.3-93.3%
            default => MoonState::NEW_MOON,                          // 93.3-100%
        };
    }
}
