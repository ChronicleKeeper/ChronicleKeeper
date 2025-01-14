<?php

declare(strict_types=1);

namespace ChronicleKeeper\Shared\Infrastructure\Database\SQLite;

use ChronicleKeeper\Shared\Infrastructure\Database\DatabasePlatform;
use ChronicleKeeper\Shared\Infrastructure\Database\Exception\UnexpectedMultipleResults;
use Psr\Log\LoggerInterface;
use SQLite3;

use function array_keys;
use function array_map;
use function count;
use function implode;
use function sprintf;

use const SQLITE3_ASSOC;

class SQLiteDatabasePlatform implements DatabasePlatform
{
    private SQLite3|null $connection = null;

    public function __construct(
        private readonly SQLiteConnectionFactory $connectionFactory,
        private readonly LoggerInterface $databaseLogger,
    ) {
    }

    private function getConnection(): SQLite3
    {
        if (! $this->connection instanceof SQLite3) {
            $this->databaseLogger->debug('Establish SQLite Connection');
            $this->connection = $this->connectionFactory->create();
        }

        return $this->connection;
    }

    /** @inheritDoc */
    public function fetch(string $sql, array $parameters = []): array
    {
        $this->databaseLogger->debug($sql, $parameters);
        $stmt = $this->getConnection()->prepare($sql);
        if ($stmt === false) {
            $this->databaseLogger->debug('Failed to prepare statement');

            return [];
        }

        foreach ($parameters as $item => $value) {
            $stmt->bindValue(':' . $item, $value);
        }

        $statementResult = $stmt->execute();

        if ($statementResult === false) {
            $this->databaseLogger->debug('Failed to execute statement');

            return [];
        }

        $result = [];
        while ($row = $statementResult->fetchArray(SQLITE3_ASSOC)) {
            $result[] = $row;
        }

        return $result;
    }

    /** @inheritDoc */
    public function fetchSingleRow(string $sql, array $parameters = []): array|null
    {
        $result = $this->fetch($sql, $parameters);
        if (count($result) > 1) {
            throw UnexpectedMultipleResults::withQuery($sql);
        }

        return $result[0] ?? null;
    }

    /** @inheritDoc */
    public function hasRows(string $table, array $parameters = []): bool
    {
        $count = $this->fetchSingleRow(sprintf(
            'SELECT COUNT(*) as count FROM %s WHERE %s',
            $table,
            implode(' AND ', array_map(static fn ($column) => $column . ' = :' . $column, array_keys($parameters))),
        ), $parameters);

        return $count !== null && $count['count'] > 0;
    }

    /** @inheritDoc */
    public function query(string $sql, array $parameters = []): void
    {
        $this->databaseLogger->debug($sql, $parameters);
        $stmt = $this->getConnection()->prepare($sql);
        if ($stmt === false) {
            $this->databaseLogger->debug('Failed to prepare statement');

            return;
        }

        foreach ($parameters as $item => $value) {
            $stmt->bindValue(':' . $item, $value);
        }

        $stmt->execute();
    }

    /** @inheritDoc */
    public function insert(string $table, array $data): void
    {
        $columns      = implode(', ', array_keys($data));
        $placeholders = ':' . implode(', :', array_keys($data));
        $sql          = sprintf('INSERT INTO %s (%s) VALUES (%s)', $table, $columns, $placeholders);

        $this->databaseLogger->debug($sql, $data);
        $stmt = $this->getConnection()->prepare($sql);
        if ($stmt === false) {
            $this->databaseLogger->debug('Failed to prepare statement');

            return;
        }

        foreach ($data as $column => $value) {
            $stmt->bindValue(':' . $column, $value);
        }

        $stmt->execute();
    }

    /** @inheritDoc */
    public function insertOrUpdate(string $table, array $data): void
    {
        $columns      = implode(', ', array_keys($data));
        $placeholders = ':' . implode(', :', array_keys($data));
        $sql          = sprintf('INSERT OR REPLACE INTO %s (%s) VALUES (%s)', $table, $columns, $placeholders);

        $this->databaseLogger->debug($sql, $data);
        $stmt = $this->getConnection()->prepare($sql);
        if ($stmt === false) {
            $this->databaseLogger->debug('Failed to prepare statement');
            return;
        }

        foreach ($data as $column => $value) {
            $stmt->bindValue(':' . $column, $value);
        }

        $stmt->execute();
    }

    public function beginTransaction(): void
    {
        $this->databaseLogger->debug('SQL: BEGIN TRANSACTION');
        $this->getConnection()->exec('BEGIN TRANSACTION');
    }

    public function commit(): void
    {
        $this->databaseLogger->debug('SQL: COMMIT');
        $this->getConnection()->exec('COMMIT');
    }

    public function rollback(): void
    {
        $this->databaseLogger->debug('SQL: ROLLBACK');
        $this->getConnection()->exec('ROLLBACK');
    }

    public function truncateTable(string $table): void
    {
        $this->query(sprintf('DELETE FROM %s', $table));
    }
}
