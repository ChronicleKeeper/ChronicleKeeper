<?php

declare(strict_types=1);

namespace ChronicleKeeper\Shared\Infrastructure\Database\PgSql\QueryBuilder;

use ChronicleKeeper\Shared\Infrastructure\Database\DatabasePlatform;
use ChronicleKeeper\Shared\Infrastructure\Database\PgSql\QueryBuilder\Traits\WhereClauseBuilder;
use ChronicleKeeper\Shared\Infrastructure\Database\QueryBuilder\DeleteQueryBuilder;
use InvalidArgumentException;

use function sprintf;
use function trim;

final class PgSqlDeleteQueryBuilder implements DeleteQueryBuilder
{
    use WhereClauseBuilder;

    private string $table = '';

    public function __construct(
        private readonly DatabasePlatform $platform,
    ) {
    }

    public function from(string $table): self
    {
        $table = trim($table);

        if ($table === '') {
            throw new InvalidArgumentException('Table name cannot be empty');
        }

        $this->table = $table;

        return $this;
    }

    public function execute(): null
    {
        if ($this->table === '') {
            throw new InvalidArgumentException('No table specified for delete query');
        }

        $this->platform->query($this->getSQL(), $this->parameters);

        return null;
    }

    private function getSQL(): string
    {
        return sprintf(
            'DELETE FROM %s%s',
            $this->table,
            $this->getWhereClause(),
        );
    }
}
