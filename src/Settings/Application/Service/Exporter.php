<?php

declare(strict_types=1);

namespace ChronicleKeeper\Settings\Application\Service;

use ChronicleKeeper\Settings\Application\Service\Exporter\ExportSettings;
use ChronicleKeeper\Settings\Application\Service\Exporter\SingleExport;
use Psr\Log\LoggerInterface;
use RuntimeException;
use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;
use ZipArchive;

use function date;
use function fclose;
use function feof;
use function file_exists;
use function fopen;
use function fread;
use function fwrite;
use function is_resource;
use function sys_get_temp_dir;
use function tempnam;
use function unlink;

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
            $this->logger->info('Executing Exporter', ['exporter' => $exporter::class]);
            $exporter->export($zip, new ExportSettings($this->version->getCurrentNumericVersion()));
        }

        $zip->close();

        return $zipName;
    }

    /** @param resource $stream The stream to write to */
    public function exportToStream($stream): void
    {
        if (! is_resource($stream)) {
            throw new RuntimeException('Invalid stream provided');
        }

        $tempFile = $this->createTemporaryFile();

        try {
            // Use existing export logic to create the ZIP
            $this->export($tempFile);
            $this->streamFileContents($tempFile, $stream);
        } finally {
            // Ensure temporary file is removed even if an exception occurs
            if (file_exists($tempFile)) {
                @unlink($tempFile);
            }
        }
    }

    private function createTemporaryFile(): string
    {
        $tempFile = tempnam(sys_get_temp_dir(), 'export_');
        if ($tempFile === false) {
            throw new RuntimeException('Failed to create temporary file');
        }

        return $tempFile;
    }

    /** @param resource $targetStream */
    private function streamFileContents(string $sourceFile, $targetStream): void
    {
        $handle = fopen($sourceFile, 'rb');
        if ($handle === false) {
            throw new RuntimeException('Failed to open export file for reading');
        }

        try {
            $this->transferData($handle, $targetStream);
        } finally {
            fclose($handle);
        }
    }

    /**
     * @param resource $source Source stream
     * @param resource $target Target stream
     */
    private function transferData($source, $target): void
    {
        while (! feof($source)) {
            $data = fread($source, 8192);
            if ($data === false) {
                throw new RuntimeException('Failed to read from export file');
            }

            $result = fwrite($target, $data);
            if ($result === false) {
                throw new RuntimeException('Failed to write to output stream');
            }
        }
    }
}
