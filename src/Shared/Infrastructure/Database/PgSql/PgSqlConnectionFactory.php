<?php

declare(strict_types=1);

namespace ChronicleKeeper\Shared\Infrastructure\Database\PgSql;

use PDO;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

final class PgSqlConnectionFactory
{
    public function __construct(
        #[Autowire('%env(string:DATABASE_CONNECTION)%')]
        private readonly string $dsn,
    ) {
    }

    public function create(): PDO
    {
        return new PDO(dsn: $this->dsn, options: [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);
    }
}
