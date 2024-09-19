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

final class VectorStorageDocumentsImporter implements SingleImport
{
    public function __construct(
        private readonly string $vectorDocumentsPath,
    ) {
    }

    public function import(Filesystem $filesystem, ImportSettings $settings): ImportedFileBag
    {
        $importedFileBag      = new ImportedFileBag();
        $libraryDirectoryPath = 'vector/document/';

        foreach ($filesystem->listContents($libraryDirectoryPath) as $zippedFile) {
            assert($zippedFile instanceof FileAttributes);

            $targetFilename = str_replace($libraryDirectoryPath, '', $zippedFile->path());
            $targetPath     = $this->vectorDocumentsPath . DIRECTORY_SEPARATOR . $targetFilename;

            if ($settings->overwriteLibrary === false && file_exists($targetPath)) {
                $importedFileBag->append(ImportedFile::asIgnored($targetFilename, FileType::VECTOR_STORAGE_DOCUMENT));
                continue;
            }

            $content = $filesystem->read($zippedFile->path());
            file_put_contents($targetPath, $content);

            $importedFileBag->append(ImportedFile::asSuccess($targetFilename, FileType::VECTOR_STORAGE_DOCUMENT));
        }

        return $importedFileBag;
    }
}
