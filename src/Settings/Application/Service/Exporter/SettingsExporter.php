<?php

declare(strict_types=1);

namespace ChronicleKeeper\Settings\Application\Service\Exporter;

use ChronicleKeeper\Settings\Application\SettingsHandler;
use ZipArchive;

use function file_exists;

final readonly class SettingsExporter implements SingleExport
{
    public function __construct(
        private string $settingsFilePath,
        private SettingsHandler $settingsHandler,
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
