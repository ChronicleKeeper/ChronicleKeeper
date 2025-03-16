<?php

declare(strict_types=1);

namespace ChronicleKeeper\Calendar\Domain\Exception;

use InvalidArgumentException;

class YearIsInvalidInCalendar extends InvalidArgumentException
{
    public static function forTooEarlyYear(int $year): self
    {
        return new self('The year ' . $year . ' is too early for the calendar.');
    }
}
