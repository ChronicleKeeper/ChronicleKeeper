<?php

declare(strict_types=1);

namespace ChronicleKeeper\Favorizer\Domain\Exception;

use InvalidArgumentException;

use function sprintf;

final class UnknownMedium extends InvalidArgumentException
{
    public static function forType(string $type): self
    {
        return new self(sprintf('Unknown target medium of type "%s"', $type));
    }

    public static function notFound(string $id, string $type): self
    {
        return new self(sprintf('Target mediumg of type "%s" with id "%s" not found.', $type, $id));
    }
}
