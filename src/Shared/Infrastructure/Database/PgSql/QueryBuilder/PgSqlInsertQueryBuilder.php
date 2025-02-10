<?php

declare(strict_types=1);

namespace ChronicleKeeper\Shared\Infrastructure\Database\PgSql\QueryBuilder;

use ChronicleKeeper\Shared\Infrastructure\Database\DatabasePlatform;
use ChronicleKeeper\Shared\Infrastructure\Database\QueryBuilder\InsertQueryBuilder;
use RuntimeException;

use function array_combine;
use function array_keys;
use function array_map;
use function array_values;
use function implode;
use function sprintf;

final class PgSqlInsertQueryBuilder implements InsertQueryBuilder
{
    private string $table;
    /** @var array<string, mixed> */
    private array $values = [];
    /** @var array<string, mixed> */
    private array $params = [];
    /** @var string[] */
    private array $conflictColumns = ['id'];
    private bool $asReplace        = false;

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

    /** @param array<string, mixed> $data */
    public function values(array $data): self
    {
        $this->values = $data;
        $this->params = array_combine(
            array_map(
                static fn (string $key) => ':' . $key,
                array_keys($data),
            ),
            array_values($data),
        );

        return $this;
    }

    /** @param string[] $columns */
    public function onConflict(array $columns): self
    {
        $this->conflictColumns = $columns;

        return $this;
    }

    public function execute(): null
    {
        $this->platform->query($this->getSQL(), $this->params);

        return null;
    }

    private function getSQL(): string
    {
        $columns = array_keys($this->values);
        $params  = array_map(
            static fn (string $col) => ':' . $col,
            $columns,
        );

        if ($this->asReplace) {
            if ($this->conflictColumns === []) {
                throw new RuntimeException('ON CONFLICT requires specifying conflict columns');
            }

            return sprintf(
                'INSERT INTO %s (%s) VALUES (%s) ON CONFLICT (%s) DO UPDATE SET %s',
                $this->table,
                implode(', ', array_map(static fn (string $col) => '"' . $col . '"', $columns)),
                implode(', ', $params),
                implode(', ', $this->conflictColumns),
                implode(
                    ', ',
                    array_map(
                        static fn (string $col) => sprintf('"%s" = EXCLUDED."%s"', $col, $col),
                        $columns,
                    ),
                ),
            );
        }

        return sprintf(
            'INSERT INTO %s (%s) VALUES (%s)',
            $this->table,
            implode(', ', array_map(static fn (string $col) => '"' . $col . '"', $columns)),
            implode(', ', $params),
        );
    }
}
