<?php

declare(strict_types=1);

namespace ChronicleKeeper\Calendar\Domain\ValueObject;

readonly class WeekDay
{
    public function __construct(
        public int $index,
        public string $name,
    ) {
    }
}
