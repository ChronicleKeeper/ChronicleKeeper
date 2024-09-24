<?php

declare(strict_types=1);

namespace ChronicleKeeper\Settings\Domain\Event;

use ChronicleKeeper\Settings\Application\Service\Importer\ImportedFile;
use ChronicleKeeper\Settings\Application\Service\ImportSettings;
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
