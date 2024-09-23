<?php

declare(strict_types=1);

namespace DZunke\NovDoc\Settings\Application\Service\Importer;

use DZunke\NovDoc\Settings\Application\Service\FileType;
use DZunke\NovDoc\Settings\Application\Service\ImportSettings;
use League\Flysystem\FileAttributes;
use League\Flysystem\Filesystem;

use function assert;
use function file_exists;
use function file_put_contents;
use function str_replace;

use const DIRECTORY_SEPARATOR;

final class LibraryImagesImporter implements SingleImport
{
    public function __construct(
        private readonly string $libraryImageStoragePath,
    ) {
    }

    public function import(Filesystem $filesystem, ImportSettings $settings): ImportedFileBag
    {
        $importedFileBag      = new ImportedFileBag();
        $libraryDirectoryPath = 'library/images/';

        foreach ($filesystem->listContents($libraryDirectoryPath) as $zippedFile) {
            assert($zippedFile instanceof FileAttributes);

            $targetFilename = str_replace($libraryDirectoryPath, '', $zippedFile->path());
            $targetPath     = $this->libraryImageStoragePath . DIRECTORY_SEPARATOR . $targetFilename;

            if ($settings->overwriteLibrary === false && file_exists($targetPath)) {
                $importedFileBag->append(ImportedFile::asIgnored($targetFilename, FileType::LIBRARY_IMAGE));
                continue;
            }

            $content = $filesystem->read($zippedFile->path());
            file_put_contents($targetPath, $content);

            $importedFileBag->append(ImportedFile::asSuccess($targetFilename, FileType::LIBRARY_IMAGE));
        }

        return $importedFileBag;
    }
}
