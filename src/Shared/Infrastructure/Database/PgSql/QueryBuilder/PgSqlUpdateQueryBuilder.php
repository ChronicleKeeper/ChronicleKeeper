<?php

declare(strict_types=1);

namespace ChronicleKeeper\Shared\Infrastructure\Database\PgSql\QueryBuilder;

use ChronicleKeeper\Shared\Infrastructure\Database\PgSql\PgSqlDatabasePlatform;
use ChronicleKeeper\Shared\Infrastructure\Database\PgSql\QueryBuilder\Traits\WhereClauseBuilder;
use ChronicleKeeper\Shared\Infrastructure\Database\QueryBuilder\UpdateQueryBuilder;

use function array_keys;
use function implode;
use function sprintf;

final class PgSqlUpdateQueryBuilder implements UpdateQueryBuilder
{
    use WhereClauseBuilder;

    private string $table = '';
    /** @var array<string, mixed> */
    private array $values = [];

    public function __construct(
        private readonly PgSqlDatabasePlatform $platform,
    ) {
    }

    public function update(string $table): self
    {
        $this->table = $table;

        return $this;
    }

    /** @param array<string, mixed> $data */
    public function set(array $data): self
    {
        $this->values = $data;
        foreach ($data as $column => $value) {
            $paramName                    = 'set_' . $column;
            $this->parameters[$paramName] = $value;
        }

        return $this;
    }

    public function execute(): null
    {
        $this->platform->query($this->getSQL(), $this->parameters);

        return null;
    }

    private function getSQL(): string
    {
        $setStatements = [];
        foreach (array_keys($this->values) as $column) {
            $paramName       = 'set_' . $column;
            $setStatements[] = sprintf('%s = :%s', $column, $paramName);
        }

        $query = sprintf(
            'UPDATE %s SET %s',
            $this->table,
            implode(', ', $setStatements),
        );

        return $query . $this->getWhereClause();
    }
}
