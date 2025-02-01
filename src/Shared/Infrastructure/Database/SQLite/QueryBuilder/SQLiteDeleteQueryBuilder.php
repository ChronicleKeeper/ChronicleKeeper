<?php

declare(strict_types=1);

namespace ChronicleKeeper\Shared\Infrastructure\Database\SQLite\QueryBuilder;

use ChronicleKeeper\Shared\Infrastructure\Database\DatabasePlatform;
use ChronicleKeeper\Shared\Infrastructure\Database\QueryBuilder\DeleteQueryBuilder;

use function implode;
use function sprintf;

final class SQLiteDeleteQueryBuilder implements DeleteQueryBuilder
{
    private string $table = '';

    /** @var list<string> */
    private array $conditions = [];
    /** @var array<string, mixed> */
    private array $parameters = [];

    public function __construct(private readonly DatabasePlatform $platform)
    {
    }

    public function from(string $table): self
    {
        $this->table = $table;

        return $this;
    }

    public function where(string $column, string $operator, mixed $value): self
    {
        $this->conditions[]        = sprintf('%s %s :%s', $column, $operator, $column);
        $this->parameters[$column] = $value;

        return $this;
    }

    public function execute(): null
    {
        $this->platform->query($this->buildQuery(), $this->parameters);

        return null;
    }

    private function buildQuery(): string
    {
        $query = sprintf('DELETE FROM %s', $this->table);

        if ($this->conditions !== []) {
            $query .= ' WHERE ' . implode(' AND ', $this->conditions);
        }

        return $query;
    }
}
