<?php

declare(strict_types=1);

namespace ChronicleKeeper\Shared\Infrastructure\Database\Exception;

use function sprintf;

final class UnambiguousResult extends DatabaseQueryException
{
    public static function forQuery(string $query): self
    {
        return new self(sprintf('Query returned more than a single row: %s', $query));
    }
}
