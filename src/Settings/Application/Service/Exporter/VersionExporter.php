<?php

declare(strict_types=1);

namespace ChronicleKeeper\Settings\Application\Service\Exporter;

use ChronicleKeeper\Settings\Application\Service\Version;
use ZipArchive;

final readonly class VersionExporter implements SingleExport
{
    public function __construct(
        private Version $version,
    ) {
    }

    public function export(ZipArchive $archive, ExportSettings $exportSettings): void
    {
        $archive->addFromString('VERSION', $this->version->getCurrentVersion());
    }
}
