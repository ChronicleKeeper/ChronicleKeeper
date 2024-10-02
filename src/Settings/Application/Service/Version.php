<?php

declare(strict_types=1);

namespace ChronicleKeeper\Settings\Application\Service;

use ChronicleKeeper\Shared\Infrastructure\Persistence\Filesystem\Contracts\FileAccess;

use function assert;
use function end;
use function explode;
use function preg_match_all;

final readonly class Version
{
    public function __construct(
        private FileAccess $fileAccess,
    ) {
    }

    public function getCurrentVersion(): string
    {
        $changelog = $this->fileAccess->read('general.project', 'CHANGELOG.md');
        assert($changelog !== '');

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
