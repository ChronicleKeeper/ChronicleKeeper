<?php

declare(strict_types=1);

namespace ChronicleKeeper\Calendar\Domain\Entity\Calendar;

final readonly class Configuration
{
    public function __construct(
        public int $beginsInYear = 0,
    ) {
    }
}
