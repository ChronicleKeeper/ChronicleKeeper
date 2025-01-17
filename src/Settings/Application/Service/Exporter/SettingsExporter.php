<?php

declare(strict_types=1);

namespace ChronicleKeeper\Settings\Application\Service\Exporter;

use ChronicleKeeper\Settings\Application\SettingsHandler;
use Psr\Log\LoggerInterface;
use Symfony\Component\Serializer\SerializerInterface;
use ZipArchive;

use const JSON_PRETTY_PRINT;
use const JSON_THROW_ON_ERROR;

final readonly class SettingsExporter implements SingleExport
{
    public function __construct(
        private LoggerInterface $logger,
        private SettingsHandler $settingsHandler,
        private SerializerInterface $serializer,
    ) {
    }

    public function export(ZipArchive $archive, ExportSettings $exportSettings): void
    {
        $settings = $this->settingsHandler->get();

        $archive->addFromString(
            'settings.json',
            $this->serializer->serialize(
                ExportData::create(
                    $exportSettings,
                    Type::SETTINGS,
                    $settings->toArray(),
                ),
                'json',
                ['json_encode_options' => JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR],
            ),
        );

        $this->logger->debug('Settings exported to "settings.json" in archive.');
    }
}
