<?php

declare(strict_types=1);

namespace ChronicleKeeper\Calendar\Domain\Entity\Calendar;

use ChronicleKeeper\Calendar\Domain\Entity\Calendar;

readonly class Month
{
    public function __construct(
        public Calendar $calendar,
        public int $indexInYear,
        public string $name,
        public DayCollection $days,
    ) {
    }
}
