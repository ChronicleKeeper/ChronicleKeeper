<?php

declare(strict_types=1);

namespace ChronicleKeeper\Settings\Domain\Event;

use ChronicleKeeper\Settings\Application\Service\ImportSettings;

final readonly class ImportFinished
{
    public function __construct(
        public ImportSettings $importSettings,
    ) {
    }
}
