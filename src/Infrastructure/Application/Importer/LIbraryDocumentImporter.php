<?php

declare(strict_types=1);

namespace DZunke\NovDoc\Infrastructure\Application\Importer;

use DZunke\NovDoc\Infrastructure\Application\FileType;
use DZunke\NovDoc\Infrastructure\Application\ImportSettings;
use League\Flysystem\FileAttributes;
use League\Flysystem\Filesystem;

use function assert;
use function file_exists;
use function file_put_contents;
use function str_replace;

use const DIRECTORY_SEPARATOR;

final class LIbraryDocumentImporter implements SingleImport
{
    public function __construct(
        private readonly string $documentStoragePath,
    ) {
    }

    public function import(Filesystem $filesystem, ImportSettings $settings): ImportedFileBag
    {
        $importedFileBag      = new ImportedFileBag();
        $libraryDirectoryPath = 'library/document/';

        foreach ($filesystem->listContents($libraryDirectoryPath) as $zippedFile) {
            assert($zippedFile instanceof FileAttributes);

            $targetFilename = str_replace($libraryDirectoryPath, '', $zippedFile->path());
            $targetPath     = $this->documentStoragePath . DIRECTORY_SEPARATOR . $targetFilename;

            if ($settings->overwriteLibrary === false && file_exists($targetPath)) {
                $importedFileBag->append(ImportedFile::asIgnored($targetFilename, FileType::LIBRARY_DOCUMENT));
                continue;
            }

            $content = $filesystem->read($zippedFile->path());
            file_put_contents($targetPath, $content);

            $importedFileBag->append(ImportedFile::asSuccess($targetFilename, FileType::LIBRARY_DOCUMENT));
        }

        return $importedFileBag;
    }
}
