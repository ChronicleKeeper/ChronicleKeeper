<?php

declare(strict_types=1);

namespace DZunke\NovDoc\Infrastructure\Application\Exporter;

use ZipArchive;

use function file_get_contents;
use function preg_match_all;

final class VersionExporter implements SingleExport
{
    public function __construct(
        private readonly string $changelogFile,
    ) {
    }

    public function export(ZipArchive $archive): void
    {
        $archive->addFromString('VERSION', $this->parseVersionFromChangelog());
    }

    private function parseVersionFromChangelog(): string
    {
        $changelog = file_get_contents($this->changelogFile);
        if ($changelog === false) {
            return 'latest'; // default version
        }

        preg_match_all('/\[(.*?)\]/', $changelog, $foundVersions);

        $foundVersions = $foundVersions[1];

        return $foundVersions[0];
    }
}
