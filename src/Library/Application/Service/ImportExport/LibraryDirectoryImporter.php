<?php

declare(strict_types=1);

namespace ChronicleKeeper\Library\Application\Service\ImportExport;

use ChronicleKeeper\Settings\Application\Service\Importer\SingleImport;
use ChronicleKeeper\Settings\Application\Service\ImportSettings;
use ChronicleKeeper\Shared\Infrastructure\Database\DatabasePlatform;
use League\Flysystem\FileAttributes;
use League\Flysystem\Filesystem;

use function assert;
use function json_decode;
use function str_replace;

use const JSON_THROW_ON_ERROR;

final readonly class LibraryDirectoryImporter implements SingleImport
{
    public function __construct(
        private DatabasePlatform $databasePlatform,
    ) {
    }

    public function import(Filesystem $filesystem, ImportSettings $settings): void
    {
        // Import with version < 0.7
        if ($filesystem->fileExists('library/directories.json') === false) {
            $this->handleOlderImports($filesystem, $settings);

            return;
        }

        $content = $filesystem->read('library/directories.json');
        $content = json_decode($content, true, 512, JSON_THROW_ON_ERROR);
        foreach ($content['data'] as $directoryArray) {
            $this->databasePlatform->insertOrUpdate(
                'directories',
                [
                    'id' => $directoryArray['id'],
                    'title' => $directoryArray['title'],
                    'parent' => $directoryArray['parent'],
                ],
            );
        }
    }

    private function handleOlderImports(Filesystem $filesystem, ImportSettings $settings): void
    {
        // Old import
        $libraryDirectoryPath = 'library/directory/';

        foreach ($filesystem->listContents($libraryDirectoryPath) as $zippedFile) {
            assert($zippedFile instanceof FileAttributes);

            $filename = str_replace($libraryDirectoryPath, '', $zippedFile->path());
            assert($filename !== '');

            $content = $filesystem->read($zippedFile->path());
            $content = json_decode($content, true, 512, JSON_THROW_ON_ERROR);

            if (
                $settings->overwriteLibrary === false
                && $this->databasePlatform->hasRows('directories', ['id' => $content['id']])
            ) {
                continue;
            }

            $this->databasePlatform->insertOrUpdate(
                'directories',
                [
                    'id' => $content['id'],
                    'title' => $content['title'],
                    'parent' => $content['parent'],
                ],
            );
        }
    }
}
