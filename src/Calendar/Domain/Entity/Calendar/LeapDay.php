<?php

declare(strict_types=1);

namespace ChronicleKeeper\Calendar\Domain\Entity\Calendar;

use Webmozart\Assert\Assert;

use function floor;
use function min;

class LeapDay implements Day
{
    public function __construct(
        public readonly int $dayOfTheMonth,
        public readonly string $name,
        public readonly int $leapYearFrequency = 1, // in Default it starts every year
        public readonly int $startYear = 0, // in Default it starts from the beginning of the calendar
        public readonly int|null $endYear = null,
    ) {
        Assert::greaterThanEq($startYear, 0, 'Start year must be greater than or equal to 0');
        if ($endYear === null) {
            return;
        }

        Assert::greaterThan($endYear, $startYear, 'End year must be greater than start year');
    }

    public function getDayOfTheMonth(): int
    {
        return $this->dayOfTheMonth;
    }

    public function getLabel(): string
    {
        return $this->name;
    }

    public function occursInYear(int $year): bool
    {
        if ($year < $this->startYear) {
            return false;
        }

        if ($this->endYear !== null && $year > $this->endYear) {
            return false;
        }

        return ($year - $this->startYear) % $this->leapYearFrequency === 0;
    }

    public function getLeapDaysSinceStart(int $targetYear): int
    {
        if ($targetYear < $this->startYear) {
            return 0;
        }

        $effectiveEndYear = $this->endYear !== null
            ? min($targetYear, $this->endYear)
            : $targetYear;

        $completedYears = $effectiveEndYear - $this->startYear;

        return (int) floor($completedYears / $this->leapYearFrequency);
    }
}
