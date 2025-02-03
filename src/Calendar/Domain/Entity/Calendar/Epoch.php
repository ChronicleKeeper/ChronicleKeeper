<?php

declare(strict_types=1);

namespace ChronicleKeeper\Calendar\Domain\Entity\Calendar;

class Epoch
{
    public function __construct(
        public readonly string $name,
        public readonly int $beginsInYear,
        public readonly int|null $endsInYear = null,
    ) {
    }
}
