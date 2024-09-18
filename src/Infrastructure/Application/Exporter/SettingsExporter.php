<?php

declare(strict_types=1);

namespace DZunke\NovDoc\Infrastructure\Application\Exporter;

use ZipArchive;

final class SettingsExporter implements SingleExport
{
    public function __construct(
        private readonly string $settingsFilePath,
    ) {
    }

    public function export(ZipArchive $archive): void
    {
        $archive->addFile($this->settingsFilePath, 'settings.json');
    }
}
