<?php

declare(strict_types=1);

namespace ChronicleKeeper\Shared\Infrastructure\Database\SQLite\QueryBuilder;

use ChronicleKeeper\Shared\Infrastructure\Database\DatabasePlatform;
use ChronicleKeeper\Shared\Infrastructure\Database\QueryBuilder\DeleteQueryBuilder;
use ChronicleKeeper\Shared\Infrastructure\Database\SQLite\QueryBuilder\Traits\WhereClauseBuilder;
use InvalidArgumentException;

use function sprintf;
use function trim;

class SQLiteDeleteQueryBuilder implements DeleteQueryBuilder
{
    use WhereClauseBuilder;

    private string $table = '';

    public function __construct(
        private readonly DatabasePlatform $platform,
    ) {
    }

    public function from(string $table): self
    {
        if (trim($table) === '') {
            throw new InvalidArgumentException('Table name cannot be empty');
        }

        $this->table = trim($table);

        return $this;
    }

    public function execute(): null
    {
        if ($this->table === '') {
            throw new InvalidArgumentException('No table specified for delete query');
        }

        $this->platform->query($this->buildQuery(), $this->parameters);

        return null;
    }

    private function buildQuery(): string
    {
        $query = sprintf('DELETE FROM %s', $this->table);

        return $query . $this->getWhereClause();
    }
}
