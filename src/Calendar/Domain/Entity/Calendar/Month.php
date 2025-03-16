<?php

declare(strict_types=1);

namespace ChronicleKeeper\Calendar\Domain\Entity\Calendar;

use ChronicleKeeper\Calendar\Domain\Entity\Calendar;
use ChronicleKeeper\Calendar\Domain\Entity\CalendarDate;

use function array_map;
use function range;

readonly class Month
{
    public function __construct(
        public Calendar $calendar,
        public int $indexInYear,
        public string $name,
        public DayCollection $days,
    ) {
    }

    /** @return list<CalendarDate> */
    public function getDaysInYear(int $year): array
    {
        return array_map(
            fn (int $day) => new CalendarDate($this->calendar, $year, $this->indexInYear, $day),
            range(1, $this->days->countInYear($year)),
        );
    }
}
