<?php

declare(strict_types=1);

namespace DZunke\NovDoc\Infrastructure\Application;

use DZunke\NovDoc\Infrastructure\Application\Exporter\SingleExport;
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
        $zipName = 'NovDoc-Export-' . date('Y-m-d-H-i-s') . '.zip';

        $zip = new ZipArchive();
        $zip->open($zipName, ZipArchive::CREATE);

        foreach ($this->exporter as $exporter) {
            $exporter->export($zip);
        }

        $zip->close();

        return $zipName;
    }
}
