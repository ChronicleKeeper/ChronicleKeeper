<?php

declare(strict_types=1);

namespace ChronicleKeeper\Calendar\Domain\Exception;

use InvalidArgumentException;

class MonthNotExists extends InvalidArgumentException
{
    public function __construct(int $index)
    {
        parent::__construct('Month with index ' . $index . ' does not exist.');
    }
}
