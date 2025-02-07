<?php

declare(strict_types=1);

namespace ChronicleKeeper\Calendar\Domain\ValueObject;

use ChronicleKeeper\Calendar\Domain\Entity\Calendar\Day;

final readonly class LeapDay implements Day
{
    public function __construct(
        public int $dayOfTheMonth,
        public string $name,
        public int $yearInterval = 1, // Default is every year
    ) {
    }

    public function getDayOfTheMonth(): int
    {
        return $this->dayOfTheMonth;
    }

    public function getLabel(): string
    {
        return $this->name;
    }

    public function isActiveInYear(int $year): bool
    {
        if ($this->yearInterval === 1) {
            return true;
        }

        return $year % $this->yearInterval === 0;
    }
}
