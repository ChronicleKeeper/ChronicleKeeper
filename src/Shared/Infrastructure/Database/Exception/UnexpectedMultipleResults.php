<?php

declare(strict_types=1);

namespace ChronicleKeeper\Shared\Infrastructure\Database\Exception;

use LogicException;

use function sprintf;

final class UnexpectedMultipleResults extends LogicException
{
    public static function withQuery(string $query): self
    {
        return new self(sprintf(
            'Unexpected multiple results for query "%s", have you considered a LIMIT?',
            $query,
        ));
    }
}
