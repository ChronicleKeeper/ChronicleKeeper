<?php

declare(strict_types=1);

namespace ChronicleKeeper\Chat\Application\Service;

use ChronicleKeeper\Settings\Application\Service\Importer\SingleImport;
use ChronicleKeeper\Settings\Application\Service\ImportSettings;
use ChronicleKeeper\Shared\Infrastructure\Persistence\Filesystem\Contracts\FileAccess;
use League\Flysystem\FileAttributes;
use League\Flysystem\Filesystem;

use function assert;
use function str_replace;

final readonly class ConversationImporter implements SingleImport
{
    public function __construct(
        private FileAccess $fileAccess,
    ) {
    }

    public function import(Filesystem $filesystem, ImportSettings $settings): void
    {
        $libraryDirectoryPath = 'library/conversations/';

        foreach ($filesystem->listContents($libraryDirectoryPath) as $zippedFile) {
            assert($zippedFile instanceof FileAttributes);

            $targetFilename = str_replace($libraryDirectoryPath, '', $zippedFile->path());
            assert($targetFilename !== '');

            if (
                $settings->overwriteLibrary === false
                && $this->fileAccess->exists('library.conversations', $targetFilename)
            ) {
                continue;
            }

            $content = $filesystem->read($zippedFile->path());
            $this->fileAccess->write('library.conversations', $targetFilename, $content);
        }
    }
}
