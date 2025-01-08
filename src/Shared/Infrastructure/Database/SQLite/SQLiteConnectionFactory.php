<?php

declare(strict_types=1);

namespace ChronicleKeeper\Shared\Infrastructure\Database\SQLite;

use RuntimeException;
use SQLite3;

use function sprintf;

use const PHP_OS_FAMILY;

final class SQLiteConnectionFactory
{
    public function __construct(
        private readonly string $databasePath,
        private readonly string $projectRoot,
    ) {
    }

    public function create(): SQLite3
    {
        $connection = new SQLite3($this->databasePath);
        $connection->enableExceptions(true);

        // Load Extensions
        $this->loadExtensionsByOS($connection);

        return $connection;
    }

    private function loadExtensionsByOS(SQLite3 $connection): void
    {
        $os = PHP_OS_FAMILY;

        if ($os === 'Windows') {
            $this->loadExtension($connection, 'vec0.dll');
        } elseif ($os === 'Linux') {
            $this->loadExtension($connection, 'vec0.so');
        } else {
            throw new RuntimeException(
                sprintf('Unsupported OS: %s', $os),
            );
        }
    }

    private function loadExtension(SQLite3 $connection, string $extension): void
    {
        $extensionFileName = $this->projectRoot . '/config/sqlite/' . $extension;

        if (! $connection->loadExtension($extensionFileName)) {
            throw new RuntimeException(
                sprintf('Failed to load SQLite extension from path: %s', $extensionFileName),
            );
        }
    }
}
