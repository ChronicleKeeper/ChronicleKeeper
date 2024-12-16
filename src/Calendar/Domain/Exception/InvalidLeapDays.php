<?php

declare(strict_types=1);

namespace ChronicleKeeper\Calendar\Domain\Exception;

use InvalidArgumentException;

use function sprintf;

class InvalidLeapDays extends InvalidArgumentException
{
    public static function leapDaysAreAlreadySet(int $monthOfTheYear): self
    {
        return new self(sprintf('Leap days are already set for month %d', $monthOfTheYear));
    }

    public static function leapDaysAreNotUnique(): self
    {
        return new self('Leap days are not unique by their index in month');
    }

    public static function leapDaysAreNotSequence(): self
    {
        return new self('Leap days are not a sequence from the max days of the month');
    }

    public static function theLeapDayDoesNotExist(int $day, int $month): self
    {
        return new self(sprintf('The leap day %d does not exist in month %d', $day, $month));
    }
}
