<?php

declare(strict_types=1);

namespace ChronicleKeeper\Settings\Application\Service;

use ChronicleKeeper\Settings\Application\Service\Exporter\SingleExport;
use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;
use ZipArchive;

use function date;

class Exporter
{
    /** @param iterable<SingleExport> $exporter */
    public function __construct(
        #[AutowireIterator('application_exporer_single_export')]
        private readonly iterable $exporter,
    ) {
    }

    public function export(): string
    {
        $zipName = 'ChronicleKeeper-Export-' . date('Y-m-d-H-i-s') . '.zip';

        $zip = new ZipArchive();
        $zip->open($zipName, ZipArchive::CREATE);

        foreach ($this->exporter as $exporter) {
            $exporter->export($zip);
        }

        $zip->close();

        return $zipName;
    }
}
