<?php

declare(strict_types=1);

namespace ChronicleKeeper\Calendar\Domain\Exception;

use InvalidArgumentException;

class YearHasNotASequentialListOfMonths extends InvalidArgumentException
{
    public function __construct()
    {
        parent::__construct('The given list of months is not sequential, so starting with the first month and ending with the last month.');
    }
}
