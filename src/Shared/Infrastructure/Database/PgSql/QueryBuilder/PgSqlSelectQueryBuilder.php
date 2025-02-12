<?php

declare(strict_types=1);

namespace ChronicleKeeper\Shared\Infrastructure\Database\PgSql\QueryBuilder;

use ChronicleKeeper\Shared\Infrastructure\Database\DatabasePlatform;
use ChronicleKeeper\Shared\Infrastructure\Database\PgSql\QueryBuilder\Traits\WhereClauseBuilder;
use ChronicleKeeper\Shared\Infrastructure\Database\QueryBuilder\SelectQueryBuilder;

use function array_map;
use function array_values;
use function implode;
use function sprintf;
use function str_contains;

final class PgSqlSelectQueryBuilder implements SelectQueryBuilder
{
    use WhereClauseBuilder;

    private string $table = '';
    /** @var array<int, string> */
    private array $columns = [];
    /** @var array<int, string> */
    private array $orderBy  = [];
    private int|null $limit = null;
    private int $offset     = 0;

    private string $vectorWhere = '';

    public function __construct(
        private readonly DatabasePlatform $platform,
    ) {
    }

    public function select(string ...$columns): self
    {
        $this->columns = array_values(array_map(
            static function (string $column): string {
                if ($column === '*' || str_contains($column, ' as ')) {
                    return $column;
                }

                return '"' . $column . '"';
            },
            $columns,
        ));

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
        return $this->platform->fetch($this->getSQL(), $this->parameters);
    }

    /** @inheritDoc */
    public function fetchAll(): array
    {
        return $this->execute();
    }

    public function fetchOneOrNull(): array|null
    {
        $this->limit = 1;

        return $this->platform->fetchOneOrNull($this->getSQL(), $this->parameters);
    }

    /** @inheritDoc */
    public function fetchOne(): array
    {
        return $this->platform->fetchOne($this->getSQL(), $this->parameters);
    }

    /** @inheritDoc */
    public function withVectorSearch(
        string $embeddingColumn,
        array $vectors,
        string $distanceColumn,
        float $maxDistance,
    ): self {
        if ($vectors === []) {
            return $this;
        }

        $vectorsToString = '[' . implode(',', $vectors) . ']';

        $this->columns[] = sprintf(
            '(%s <=> \'%s\') AS %s',
            $embeddingColumn,
            $vectorsToString,
            $distanceColumn,
        );

        $this->vectorWhere = sprintf(
            '(%s <=> \'%s\') < %s',
            $embeddingColumn,
            $vectorsToString,
            $maxDistance,
        );

        return $this;
    }

    public function vectorToJson(string $embeddingColumn, string $outputAlias): self
    {
        $this->columns[] = sprintf('%s as %s', $embeddingColumn, $outputAlias);

        return $this;
    }

    public function getSQL(): string
    {
        $sql = sprintf(
            'SELECT %s FROM %s',
            $this->columns === [] ? '*' : implode(', ', $this->columns),
            $this->table,
        );

        $sql .= $this->getWhereClause();

        if ($this->vectorWhere !== '' && str_contains($sql, 'WHERE')) {
            $sql .= ' AND ' . $this->vectorWhere;
        } elseif ($this->vectorWhere !== '') {
            $sql .= ' WHERE ' . $this->vectorWhere;
        }

        if ($this->orderBy !== []) {
            $sql .= ' ORDER BY ' . implode(', ', $this->orderBy);
        }

        if ($this->limit !== null) {
            $sql .= ' LIMIT ' . $this->limit;
        }

        if ($this->offset > 0) {
            $sql .= ' OFFSET ' . $this->offset;
        }

        return $sql;
    }
}
