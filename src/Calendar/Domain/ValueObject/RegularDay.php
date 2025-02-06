<?php

declare(strict_types=1);

namespace ChronicleKeeper\Calendar\Domain\ValueObject;

use ChronicleKeeper\Calendar\Domain\Entity\Calendar\Day;

final readonly class RegularDay implements Day
{
    public function __construct(
        public int $dayOfTheMonth,
        public int $dayToDisplay,
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
