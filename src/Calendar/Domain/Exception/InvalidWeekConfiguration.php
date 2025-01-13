<?php

declare(strict_types=1);

namespace ChronicleKeeper\Calendar\Domain\Exception;

use RuntimeException;

class InvalidWeekConfiguration extends RuntimeException
{
    public static function emptyWeekDays(): self
    {
        return new self('Week configuration must contain at least one day');
    }

    public static function weekDaysNotSequential(): self
    {
        return new self('Week days must be sequential starting from 1');
    }
}
