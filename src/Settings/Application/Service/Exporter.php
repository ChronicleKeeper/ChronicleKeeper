<?php

declare(strict_types=1);

namespace ChronicleKeeper\Settings\Application\Service;

use ChronicleKeeper\Settings\Application\Service\Exporter\ExportSettings;
use ChronicleKeeper\Settings\Application\Service\Exporter\SingleExport;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;
use ZipArchive;

use function date;

class Exporter
{
    /** @param iterable<SingleExport> $exporter */
    public function __construct(
        #[AutowireIterator('application_exporer_single_export')]
        private readonly iterable $exporter,
        private readonly LoggerInterface $logger,
        private readonly Version $version,
    ) {
    }

    public function export(string|null $filename = null): string
    {
        $zipName = $filename ?? 'ChronicleKeeper-Export-' . date('Y-m-d-H-i-s') . '.zip';

        $zip = new ZipArchive();
        $zip->open($zipName, ZipArchive::CREATE);

        foreach ($this->exporter as $exporter) {
            $this->logger->info('Executing exporte of type: ' . $exporter::class);
            $exporter->export($zip, new ExportSettings($this->version->getCurrentNumericVersion()));
            $this->logger->info('Export of type: ' . $exporter::class . ' was executed');
        }

        $zip->close();

        return $zipName;
    }
}
