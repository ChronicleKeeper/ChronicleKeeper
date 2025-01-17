<?php

declare(strict_types=1);

namespace ChronicleKeeper\Settings\Application\Service\Exporter;

readonly class ExportSettings
{
    public function __construct(
        public string $appVersion,
    ) {
    }
}
