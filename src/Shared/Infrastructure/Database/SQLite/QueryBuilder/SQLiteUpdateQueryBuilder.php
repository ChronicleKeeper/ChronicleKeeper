<?php

declare(strict_types=1);

namespace ChronicleKeeper\Shared\Infrastructure\Database\SQLite\QueryBuilder;

use ChronicleKeeper\Shared\Infrastructure\Database\DatabasePlatform;
use ChronicleKeeper\Shared\Infrastructure\Database\QueryBuilder\UpdateQueryBuilder;

use function array_keys;
use function array_map;
use function count;
use function implode;
use function sprintf;

final class SQLiteUpdateQueryBuilder implements UpdateQueryBuilder
{
    private string $table = '';
    /** @var array<string, mixed> */
    private array $values = [];
    /** @var list<string> */
    private array $conditions = [];
    /** @var array<string, mixed> */
    private array $parameters = [];

    public function __construct(private readonly DatabasePlatform $platform)
    {
    }

    public function update(string $table): self
    {
        $this->table = $table;

        return $this;
    }

    /** @inheritDoc */
    public function set(array $data): self
    {
        $this->values = $data;
        foreach ($data as $column => $value) {
            $this->parameters[$column] = $value;
        }

        return $this;
    }

    public function where(string $column, string $operator, mixed $value): self
    {
        $paramName                    = $column . '_' . count($this->parameters);
        $this->conditions[]           = sprintf('%s %s :%s', $column, $operator, $paramName);
        $this->parameters[$paramName] = $value;

        return $this;
    }

    public function execute(): null
    {
        $this->platform->query($this->buildQuery(), $this->parameters);

        return null;
    }

    private function buildQuery(): string
    {
        $sets = array_map(
            static fn ($column) => sprintf('%s = :%s', $column, $column),
            array_keys($this->values),
        );

        $query = sprintf(
            'UPDATE %s SET %s',
            $this->table,
            implode(', ', $sets),
        );

        if ($this->conditions !== []) {
            $query .= ' WHERE ' . implode(' AND ', $this->conditions);
        }

        return $query;
    }
}
