<?php

declare(strict_types=1);

namespace DZunke\NovDoc\Infrastructure\Application\Importer;

use DZunke\NovDoc\Infrastructure\Application\FileType;
use League\Flysystem\Filesystem;

use function file_put_contents;

final class SettingsImporter implements SingleImport
{
    public function __construct(
        private readonly string $settingsFilePath,
    ) {
    }

    public function import(Filesystem $filesystem): ImportedFileBag
    {
        // Other then the files from the library the settings will be always overwritten - Settings kommen noch!
        $content = $filesystem->read('settings.json');
        file_put_contents($this->settingsFilePath, $content);

        return new ImportedFileBag(ImportedFile::asSuccess($this->settingsFilePath, FileType::SETTINGS));
    }
}
