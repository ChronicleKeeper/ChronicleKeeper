<?php

declare(strict_types=1);

namespace ChronicleKeeper\Shared\Infrastructure\Database\Exception;

use Throwable;

use function sprintf;

final class QueryExecutionFailed extends DatabaseQueryException
{
    public static function forQuery(string $query, Throwable|null $previous = null): self
    {
        return new self(sprintf('Query execution failed: %s', $query), previous: $previous);
    }

    public static function forStatement(string $query, string $error, Throwable|null $previous = null): self
    {
        return new self(sprintf('Query execution failed: %s. Error: %s', $query, $error), previous: $previous);
    }
}
