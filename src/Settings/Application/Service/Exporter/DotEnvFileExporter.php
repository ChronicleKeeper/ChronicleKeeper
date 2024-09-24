<?php

declare(strict_types=1);

namespace ChronicleKeeper\Settings\Application\Service\Exporter;

use ZipArchive;

final class DotEnvFileExporter implements SingleExport
{
    public function __construct(
        private readonly string $dotEnvFile,
    ) {
    }

    public function export(ZipArchive $archive): void
    {
        $archive->addFile($this->dotEnvFile, '.env');
    }
}
