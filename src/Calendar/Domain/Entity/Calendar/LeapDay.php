<?php

declare(strict_types=1);

namespace ChronicleKeeper\Calendar\Domain\Entity\Calendar;

class LeapDay implements Day
{
    public function __construct(
        public readonly int $dayOfTheMonth,
        public readonly string $name,
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
}
