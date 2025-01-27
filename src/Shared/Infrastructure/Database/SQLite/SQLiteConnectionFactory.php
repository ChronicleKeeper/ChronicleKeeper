<?php

declare(strict_types=1);

namespace ChronicleKeeper\Shared\Infrastructure\Database\SQLite;

use RuntimeException;
use SQLite3;

use function sprintf;
use function substr;

use const DIRECTORY_SEPARATOR;
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
        $extensionFileName = $this->projectRoot
            . DIRECTORY_SEPARATOR . 'config'
            . DIRECTORY_SEPARATOR . 'sqlite'
            . DIRECTORY_SEPARATOR . $extension;

        if (PHP_OS_FAMILY === 'Windows') {
            /**
             * Because the extension loader prefixes anything with Backslash we have to remove the drive from
             * the extension path. This will result in an incompatibility for running the application in other
             * drives than C: but this is a limitation of the SQLite3 extension loader on Windows until we
             * may find another solution with an installer that is automatically changing the php.ini file.
             */
            $extensionFileName = substr($extensionFileName, 3);
        }

        if (! $connection->loadExtension($extensionFileName)) {
            throw new RuntimeException(
                sprintf('Failed to load SQLite extension from path: %s', $extensionFileName),
            );
        }
    }
}
