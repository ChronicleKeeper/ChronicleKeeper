<?php

declare(strict_types=1);

namespace ChronicleKeeper\Settings\Application\Service\Exporter;

use JsonSerializable;

class ExportData implements JsonSerializable
{
    private string $appVersion;
    private Type $type;

    /** @var array<int|string, mixed> */
    private array $data = [];

    private function __construct()
    {
        // Construction is disabled by design
    }

    /** @param array<int|string, mixed> $data */
    public static function create(ExportSettings $exportSettings, Type $type, array $data): self
    {
        $exportData             = new self();
        $exportData->appVersion = $exportSettings->appVersion;
        $exportData->type       = $type;
        $exportData->data       = $data;

        return $exportData;
    }

    /** @return array<int|string, mixed> */
    public function jsonSerialize(): array
    {
        return [
            'appVersion' => $this->appVersion,
            'type' => $this->type->value,
            'data' => $this->data,
        ];
    }
}
