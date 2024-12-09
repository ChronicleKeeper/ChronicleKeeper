<?php

declare(strict_types=1);

namespace ChronicleKeeper\Calendar\Domain\Entity\Calendar;

use ChronicleKeeper\Calendar\Domain\Entity\Calendar;

class Month
{
    public function __construct(
        public readonly Calendar $calendar,
        public readonly int $indexInYear,
        public readonly string $name,
        public readonly int $numberOfDays, // For leap days this then has to be in a special method
    ) {
    }
}
