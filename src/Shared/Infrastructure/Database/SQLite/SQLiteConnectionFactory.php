<?php

declare(strict_types=1);

namespace ChronicleKeeper\Shared\Infrastructure\Database\SQLite;

use SQLite3;

final class SQLiteConnectionFactory
{
    public function __construct(
        private readonly string $databasePath,
    ) {
    }

    public function create(): SQLite3
    {
        $connection = new SQLite3($this->databasePath);
        $connection->enableExceptions(true);

        return $connection;
    }
}
