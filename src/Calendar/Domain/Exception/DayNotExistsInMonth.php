<?php

declare(strict_types=1);

namespace ChronicleKeeper\Calendar\Domain\Exception;

use InvalidArgumentException;

use function sprintf;

class DayNotExistsInMonth extends InvalidArgumentException
{
    public function __construct(int $year, int $month, int $day)
    {
        parent::__construct(
            sprintf('Day %d does not exist in month %d of year %d', $day, $month, $year),
        );
    }
}
