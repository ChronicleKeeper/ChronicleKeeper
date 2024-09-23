<?php

declare(strict_types=1);

namespace DZunke\NovDoc\Settings\Application\Service;

use function end;
use function explode;
use function file_get_contents;
use function preg_match_all;

final class Version
{
    public function __construct(
        private readonly string $changelogFile,
    ) {
    }

    public function getCurrentVersion(): string
    {
        $changelog = file_get_contents($this->changelogFile);
        if ($changelog === false) {
            return 'dev'; // default version
        }

        preg_match_all('/\[(.*?)\]/', $changelog, $foundVersions);

        $foundVersions = $foundVersions[1];

        return $foundVersions[0];
    }

    public function getCurrentNumericVersion(): string
    {
        return $this->parseToNumericVersion($this->getCurrentVersion());
    }

    public function parseToNumericVersion(string $version): string
    {
        $versionPieces = explode('-', $version);

        return end($versionPieces);
    }
}
