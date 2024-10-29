<?php

declare(strict_types=1);

namespace ChronicleKeeper\Library\Application\Service\Migrator;

use ChronicleKeeper\Library\Domain\RootDirectory;
use ChronicleKeeper\Settings\Application\Service\FileType;
use ChronicleKeeper\Settings\Application\Service\Migrator\FileMigration;
use Symfony\Component\Filesystem\Filesystem;

use function array_key_exists;
use function is_array;
use function json_decode;
use function json_encode;

use const JSON_PRETTY_PRINT;
use const JSON_THROW_ON_ERROR;

final readonly class FixEmptyDirectory implements FileMigration
{
    public function __construct(
        private Filesystem $filesystem,
    ) {
    }

    public function isSupporting(FileType $type, string $fileVersion): bool
    {
        // Do it on every import for documents and images
        return $type === FileType::LIBRARY_DOCUMENT
            || $type === FileType::LIBRARY_IMAGE;
    }

    public function migrate(string $file): void
    {
        $fileContent = $this->filesystem->readFile($file);
        $jsonArr     = json_decode($fileContent, true, 512, JSON_THROW_ON_ERROR);

        if (
            ! is_array($jsonArr)
            || array_key_exists('directory', $jsonArr)
        ) {
            // Nothing to do - there is a directory
            return;
        }

        $jsonArr['directory'] = RootDirectory::ID;

        $this->filesystem->dumpFile($file, json_encode($jsonArr, JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT));
    }
}
