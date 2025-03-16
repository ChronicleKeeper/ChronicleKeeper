<?php

declare(strict_types=1);

namespace ChronicleKeeper\Calendar\Domain\Exception;

use InvalidArgumentException;

class YearIsNotStartingWithFirstMonth extends InvalidArgumentException
{
    public function __construct()
    {
        parent::__construct('The given list of months is not starting with a first month, first index should be "1".');
    }
}
