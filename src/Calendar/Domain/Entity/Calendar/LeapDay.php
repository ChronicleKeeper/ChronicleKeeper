<?php

declare(strict_types=1);

namespace ChronicleKeeper\Calendar\Domain\Entity\Calendar;

class LeapDay
{
    public function __construct(
        public readonly int $dayOfTheMonth,
        public readonly string $name,
    ) {
    }
}
