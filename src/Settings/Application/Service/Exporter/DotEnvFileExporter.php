<?php

declare(strict_types=1);

namespace ChronicleKeeper\Settings\Application\Service\Exporter;

use ChronicleKeeper\Shared\Infrastructure\Persistence\Filesystem\PathRegistry;
use ZipArchive;

use const DIRECTORY_SEPARATOR;

final readonly class DotEnvFileExporter implements SingleExport
{
    public function __construct(
        private PathRegistry $pathRegistry,
    ) {
    }

    public function export(ZipArchive $archive): void
    {
        $archive->addFile(
            $this->pathRegistry->get('general.project') . DIRECTORY_SEPARATOR . '.env',
            '.env',
        );
    }
}
