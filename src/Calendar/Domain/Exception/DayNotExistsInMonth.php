<?php

declare(strict_types=1);

namespace ChronicleKeeper\Calendar\Domain\Exception;

use InvalidArgumentException;

use function sprintf;

class DayNotExistsInMonth extends InvalidArgumentException
{
    public static function forDayAtSpecificDate(int $year, int $month, int $day): self
    {
        return new self(sprintf('Day %d does not exist in month %d of year %d', $day, $month, $year));
    }

    public static function forDayGenerallyNotExistsInMonth(int $day): self
    {
        return new self(sprintf('Day %d does not exist in the month, in any year', $day));
    }
}
