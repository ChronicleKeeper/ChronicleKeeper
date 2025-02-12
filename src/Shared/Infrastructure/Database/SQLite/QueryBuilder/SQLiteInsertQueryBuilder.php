<?php

declare(strict_types=1);

namespace ChronicleKeeper\Shared\Infrastructure\Database\SQLite\QueryBuilder;

use ChronicleKeeper\Shared\Infrastructure\Database\DatabasePlatform;
use ChronicleKeeper\Shared\Infrastructure\Database\QueryBuilder\InsertQueryBuilder;

use function array_keys;
use function array_map;
use function implode;
use function sprintf;

final class SQLiteInsertQueryBuilder implements InsertQueryBuilder
{
    private string $table = '';
    /** @var array<string, mixed> */
    private array $values   = [];
    private bool $asReplace = false;

    public function __construct(private readonly DatabasePlatform $platform)
    {
    }

    public function insert(string $table): self
    {
        $this->table = $table;

        return $this;
    }

    public function asReplace(): self
    {
        $this->asReplace = true;

        return $this;
    }

    /** @inheritDoc */
    public function values(array $data): self
    {
        $this->values = $data;

        return $this;
    }

    /** @inheritDoc */
    public function onConflict(array $columns): self
    {
        return $this;
    }

    public function execute(): null
    {
        $this->platform->query($this->buildQuery(), $this->values);

        return null;
    }

    private function buildQuery(): string
    {
        $columns      = array_keys($this->values);
        $placeholders = array_map(static fn ($column) => ':' . $column, $columns);

        $queryType = $this->asReplace ? 'REPLACE INTO' : 'INSERT INTO';

        return sprintf(
            '%s %s (%s) VALUES (%s)',
            $queryType,
            $this->table,
            implode(', ', $columns),
            implode(', ', $placeholders),
        );
    }
}
