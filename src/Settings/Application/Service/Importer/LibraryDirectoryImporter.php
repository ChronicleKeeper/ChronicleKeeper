<?php

declare(strict_types=1);

namespace ChronicleKeeper\Settings\Application\Service\Importer;

use ChronicleKeeper\Settings\Application\Service\FileType;
use ChronicleKeeper\Settings\Application\Service\ImportSettings;
use ChronicleKeeper\Shared\Infrastructure\Persistence\Filesystem\Contracts\FileAccess;
use League\Flysystem\FileAttributes;
use League\Flysystem\Filesystem;

use function assert;
use function str_replace;

final readonly class LibraryDirectoryImporter implements SingleImport
{
    public function __construct(
        private FileAccess $fileAccess,
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

            if ($settings->overwriteLibrary === false && $this->fileAccess->exists('library.directories', $filename)) {
                $importedFileBag->append(ImportedFile::asIgnored($filename, FileType::LIBRARY_DIRECTORY));
                continue;
            }

            $content = $filesystem->read($zippedFile->path());
            $this->fileAccess->write('library.directories', $filename, $content);

            $importedFileBag->append(ImportedFile::asSuccess($filename, FileType::LIBRARY_DIRECTORY));
        }

        return $importedFileBag;
    }
}
