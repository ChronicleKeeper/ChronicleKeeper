<?php

declare(strict_types=1);

namespace ChronicleKeeper\Library\Application\Service\Importer;

use ChronicleKeeper\Settings\Application\Service\FileType;
use ChronicleKeeper\Settings\Application\Service\Importer\ImportedFile;
use ChronicleKeeper\Settings\Application\Service\Importer\ImportedFileBag;
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

    public function import(Filesystem $filesystem, ImportSettings $settings): ImportedFileBag
    {
        $importedFileBag      = new ImportedFileBag();
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
                $importedFileBag->append(ImportedFile::asIgnored($filename, FileType::LIBRARY_DIRECTORY));
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

            $importedFileBag->append(ImportedFile::asSuccess($filename, FileType::LIBRARY_DIRECTORY));
        }

        return $importedFileBag;
    }
}
