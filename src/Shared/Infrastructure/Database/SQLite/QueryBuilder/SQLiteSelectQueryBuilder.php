<?php

declare(strict_types=1);

namespace ChronicleKeeper\Shared\Infrastructure\Database\SQLite\QueryBuilder;

use ChronicleKeeper\Shared\Infrastructure\Database\DatabasePlatform;
use ChronicleKeeper\Shared\Infrastructure\Database\QueryBuilder\SelectQueryBuilder;

use function count;
use function implode;
use function sprintf;

final class SQLiteSelectQueryBuilder implements SelectQueryBuilder
{
    /** @var array<int|string, non-empty-string> */
    private array $columns = ['*'];
    private string $table  = '';
    /** @var list<string> */
    private array $conditions = [];
    /** @var array<string, mixed> */
    private array $parameters = [];
    private int|null $limit   = null;
    private int|null $offset  = null;
    /** @var string[] */
    private array $orderBy = [];

    public function __construct(private readonly DatabasePlatform $platform)
    {
    }

    public function select(string ...$columns): self
    {
        $this->columns = $columns;

        return $this;
    }

    public function from(string $table): self
    {
        $this->table = $table;

        return $this;
    }

    public function where(string $column, string $operator, mixed $value): self
    {
        $paramName                    = $column . '_' . count($this->parameters);
        $this->conditions[]           = sprintf('%s %s :%s', $column, $operator, $paramName);
        $this->parameters[$paramName] = $value;

        return $this;
    }

    public function orderBy(string $column, string $direction = 'ASC'): self
    {
        $this->orderBy[] = sprintf('%s %s', $column, $direction);

        return $this;
    }

    public function limit(int $limit): self
    {
        $this->limit = $limit;

        return $this;
    }

    public function offset(int $offset): self
    {
        $this->offset = $offset;

        return $this;
    }

    /** @return list<array<string, mixed>> */
    public function execute(): array
    {
        return $this->platform->fetch($this->buildQuery(), $this->parameters);
    }

    /** @inheritDoc */
    public function fetchAll(): array
    {
        return $this->execute();
    }

    public function fetchOne(): array|null
    {
        $this->limit(1);
        $result = $this->execute();

        return $result[0] ?? null;
    }

    private function buildQuery(): string
    {
        $query = sprintf('SELECT %s FROM %s', implode(', ', $this->columns), $this->table);

        if ($this->conditions !== []) {
            $query .= ' WHERE ' . implode(' AND ', $this->conditions);
        }

        if ($this->orderBy !== []) {
            $query .= ' ORDER BY ' . implode(', ', $this->orderBy);
        }

        if ($this->limit !== null) {
            $query .= sprintf(' LIMIT %d', $this->limit);
        }

        if ($this->offset !== null) {
            $query .= sprintf(' OFFSET %d', $this->offset);
        }

        return $query;
    }
}
