<?php

declare(strict_types=1);

namespace ChronicleKeeper\Shared\Infrastructure\Database\Exception;

use Throwable;

use function sprintf;

final class StatementPreparationFailed extends DatabaseQueryException
{
    public static function forQuery(string $query, Throwable|null $previous = null): self
    {
        return new self(sprintf('Statement preparation failed: %s', $query), previous: $previous);
    }
}
