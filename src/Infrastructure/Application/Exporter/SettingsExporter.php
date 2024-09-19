<?php

declare(strict_types=1);

namespace DZunke\NovDoc\Infrastructure\Application\Exporter;

use DZunke\NovDoc\Domain\Settings\SettingsHandler;
use ZipArchive;

use function file_exists;

final class SettingsExporter implements SingleExport
{
    public function __construct(
        private readonly string $settingsFilePath,
        private readonly SettingsHandler $settingsHandler,
    ) {
    }

    public function export(ZipArchive $archive): void
    {
        if (! file_exists($this->settingsFilePath)) {
            // Create the settings file if it does not exist at this time ... it is not initially created with defaults
            $this->settingsHandler->store();
        }

        $archive->addFile($this->settingsFilePath, 'settings.json');
    }
}
