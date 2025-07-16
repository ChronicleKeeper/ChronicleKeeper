<?php

declare(strict_types=1);

namespace ChronicleKeeper\Settings\Application\Service\Exporter;

class ExportData
{
    public string $appVersion;
    public Type $type;

    /** @var array<int|string, mixed>|object */
    public array|object $data;

    private function __construct()
    {
        // Construction is disabled by design
    }

    /** @param array<int|string, mixed> $data */
    public static function create(ExportSettings $exportSettings, Type $type, array|object $data): self
    {
        $exportData             = new self();
        $exportData->appVersion = $exportSettings->appVersion;
        $exportData->type       = $type;
        $exportData->data       = $data;

        return $exportData;
    }
}
