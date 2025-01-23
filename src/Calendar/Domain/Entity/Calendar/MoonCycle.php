<?php

declare(strict_types=1);

namespace ChronicleKeeper\Calendar\Domain\Entity\Calendar;

use ChronicleKeeper\Calendar\Domain\Entity\CalendarDate;
use ChronicleKeeper\Calendar\Domain\ValueObject\MoonState;
use Webmozart\Assert\Assert;

use function cos;

use const M_PI;

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
        $dayInCycle = $totalDays % $this->daysOfACycle;

        // Calculate percentage (0-100) and convert to radians (0-2π)
        $angleInRadians = $dayInCycle / $this->daysOfACycle * 2 * M_PI;

        // Use cosine to get illumination percentage (-1 to 1, converted to 0-100)
        $illumination = (1 - cos($angleInRadians)) * 50;

        // More precise phase boundaries
        return match (true) {
            $illumination <= 2 => MoonState::NEW_MOON,           // 0-2%
            $illumination < 48 => MoonState::WAXING_CRESCENT,    // 2-48%
            $illumination < 52 => MoonState::FIRST_QUARTER,      // 48-52%
            $illumination < 98 => MoonState::WAXING_GIBBOUS,     // 52-98%
            $illumination <= 100 => MoonState::FULL_MOON,        // 98-100%
            $illumination > 98 => MoonState::WANING_GIBBOUS,     // 100-98%
            $illumination > 48 => MoonState::LAST_QUARTER,       // 98-48%
            default => MoonState::WANING_CRESCENT                // 48-2%
        };
    }
}
