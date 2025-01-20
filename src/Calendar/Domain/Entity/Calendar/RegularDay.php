<?php

declare(strict_types=1);

namespace ChronicleKeeper\Calendar\Domain\Entity\Calendar;

class RegularDay implements Day
{
    public function __construct(
        public readonly int $dayOfTheMonth,
        public readonly int $dayToDisplay,
    ) {
    }

    public function getDayOfTheMonth(): int
    {
        return $this->dayOfTheMonth;
    }

    public function getLabel(): string
    {
        return (string) $this->dayToDisplay;
    }
}
