<?php

declare(strict_types=1);

namespace ChronicleKeeper\Library\Application\Service\Migrator;

use ChronicleKeeper\Settings\Application\Service\FileType;
use ChronicleKeeper\Settings\Application\Service\Migrator\FileMigration;
use Symfony\Component\Filesystem\Filesystem;

use function version_compare;

final readonly class ClearVectorStorage implements FileMigration
{
    public function __construct(
        private Filesystem $filesystem,
    ) {
    }

    public function isSupporting(FileType $type, string $fileVersion): bool
    {
        if (version_compare($fileVersion, '0.5') >= 0) {
            return false;
        }

        return $type === FileType::VECTOR_STORAGE_IMAGE
            || $type === FileType::VECTOR_STORAGE_DOCUMENT;
    }

    public function migrate(string $file, FileType $type): void
    {
        // Just remove the file because we want a re-index triggered
        $this->filesystem->remove($file);
    }
}
