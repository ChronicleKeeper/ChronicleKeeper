<?php

declare(strict_types=1);

namespace DZunke\NovDoc\Infrastructure\Event;

use DZunke\NovDoc\Infrastructure\Application\Importer\ImportedFile;
use DZunke\NovDoc\Infrastructure\Application\ImportSettings;
use Symfony\Contracts\EventDispatcher\Event;

final class FileImported extends Event
{
    public function __construct(
        public readonly ImportSettings $importSettings,
        public readonly ImportedFile $importedFile,
        public readonly string $version,
    ) {
    }
}
