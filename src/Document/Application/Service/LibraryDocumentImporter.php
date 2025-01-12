<?php

declare(strict_types=1);

namespace ChronicleKeeper\Document\Application\Service;

use ChronicleKeeper\Settings\Application\Service\Importer\SingleImport;
use ChronicleKeeper\Settings\Application\Service\ImportSettings;
use ChronicleKeeper\Shared\Infrastructure\Database\DatabasePlatform;
use League\Flysystem\FileAttributes;
use League\Flysystem\Filesystem;

use function assert;
use function json_decode;
use function str_replace;

use const JSON_THROW_ON_ERROR;

final readonly class LibraryDocumentImporter implements SingleImport
{
    public function __construct(
        private DatabasePlatform $databasePlatform,
    ) {
    }

    public function import(Filesystem $filesystem, ImportSettings $settings): void
    {
        $libraryDirectoryPath = 'library/document/';

        foreach ($filesystem->listContents($libraryDirectoryPath) as $zippedFile) {
            assert($zippedFile instanceof FileAttributes);

            $filename = str_replace($libraryDirectoryPath, '', $zippedFile->path());
            assert($filename !== '');

            $content = $filesystem->read($zippedFile->path());
            $content = json_decode($content, true, 512, JSON_THROW_ON_ERROR);

            if (
                $settings->overwriteLibrary === false
                && $this->databasePlatform->hasRows('documents', ['id' => $content['id']])
            ) {
                continue;
            }

            $this->databasePlatform->insertOrUpdate(
                'documents',
                [
                    'id' => $content['id'],
                    'title' => $content['title'],
                    'content' => $content['content'],
                    'directory' => $content['directory'],
                    'last_updated' => $content['last_updated'],
                ],
            );
        }
    }
}
