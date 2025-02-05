<?php

declare(strict_types=1);

namespace ChronicleKeeper\Shared\Infrastructure\Database\SQLite\QueryBuilder;

use ChronicleKeeper\Shared\Infrastructure\Database\DatabasePlatform;
use ChronicleKeeper\Shared\Infrastructure\Database\QueryBuilder\SelectQueryBuilder;
use ChronicleKeeper\Shared\Infrastructure\Database\SQLite\QueryBuilder\Traits\WhereClauseBuilder;

use function implode;
use function sprintf;

final class SQLiteSelectQueryBuilder implements SelectQueryBuilder
{
    use WhereClauseBuilder;

    /** @var array<int|string, non-empty-string> */
    private array $columns   = ['*'];
    private string $table    = '';
    private int|null $limit  = null;
    private int|null $offset = null;
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

    /** @return array<int, array<string, mixed>> */
    public function execute(): array
    {
        return $this->platform->fetch($this->buildQuery(), $this->parameters);
    }

    /** @inheritDoc */
    public function fetchAll(): array
    {
        return $this->platform->fetch($this->buildQuery(), $this->parameters);
    }

    public function fetchOneOrNull(): array|null
    {
        $this->limit = 1;

        return $this->platform->fetchOneOrNull($this->buildQuery(), $this->parameters);
    }

    /** @inheritDoc */
    public function fetchOne(): array
    {
        return $this->platform->fetchOne($this->buildQuery(), $this->parameters);
    }

    private function buildQuery(): string
    {
        $query  = sprintf('SELECT %s FROM %s', implode(', ', $this->columns), $this->table);
        $query .= $this->getWhereClause();

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
