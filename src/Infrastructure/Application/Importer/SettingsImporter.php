<?php

declare(strict_types=1);

namespace DZunke\NovDoc\Infrastructure\Application\Importer;

use DZunke\NovDoc\Infrastructure\Application\FileType;
use DZunke\NovDoc\Infrastructure\Application\ImportSettings;
use League\Flysystem\Filesystem;

use function file_exists;
use function file_put_contents;

final class SettingsImporter implements SingleImport
{
    public function __construct(
        private readonly string $settingsFilePath,
    ) {
    }

    public function import(Filesystem $filesystem, ImportSettings $settings): ImportedFileBag
    {
        if ($settings->overwriteSettings === false && file_exists($this->settingsFilePath)) {
            return new ImportedFileBag(ImportedFile::asIgnored($this->settingsFilePath, FileType::SETTINGS));
        }

        $content = $filesystem->read('settings.json');
        file_put_contents($this->settingsFilePath, $content);

        return new ImportedFileBag(ImportedFile::asSuccess($this->settingsFilePath, FileType::SETTINGS));
    }
}
