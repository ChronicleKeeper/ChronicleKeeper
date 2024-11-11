<?php

declare(strict_types=1);

namespace ChronicleKeeper\Favorizer\Domain\Exception;

use RuntimeException;

use function sprintf;

final class MaxTargetsInBag extends RuntimeException
{
    public static function reached(int $maxTargets): self
    {
        return new self(sprintf('The target bag reached the maximum number of targets (%d).', $maxTargets));
    }
}
