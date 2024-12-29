<?php

declare(strict_types=1);

namespace ChronicleKeeper\Calendar\Domain\Exception;

use InvalidArgumentException;

class DayNotExistsInMonth extends InvalidArgumentException
{
    public function __construct(int $month, int $day)
    {
        parent::__construct('Day with index ' . $day . ' does not exist in month with index ' . $month . '.');
    }
}
