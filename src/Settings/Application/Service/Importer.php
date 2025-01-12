<?php

declare(strict_types=1);

namespace ChronicleKeeper\Settings\Application\Service;

use ChronicleKeeper\Settings\Application\Service\Importer\ImportedFileBag;
use ChronicleKeeper\Settings\Application\Service\Importer\SingleImport;
use ChronicleKeeper\Settings\Domain\Event\ExecuteImportPruning;
use ChronicleKeeper\Settings\Domain\Event\FileImported;
use ChronicleKeeper\Settings\Domain\Event\ImportFinished;
use League\Flysystem\Filesystem;
use League\Flysystem\ZipArchive\FilesystemZipArchiveProvider;
use League\Flysystem\ZipArchive\ZipArchiveAdapter;
use Psr\Log\LoggerInterface;
use RuntimeException;
use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

use function unlink;
use function version_compare;

class Importer
{
    /** @param iterable<SingleImport> $importer */
    public function __construct(
        #[AutowireIterator('application_exporer_single_import')]
        private readonly iterable $importer,
        private readonly Version $version,
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly LoggerInterface $logger,
    ) {
    }

    public function import(string $archiveFile, ImportSettings $importSettings): ImportedFileBag
    {
        $adapter                   = new ZipArchiveAdapter(new FilesystemZipArchiveProvider($archiveFile));
        $filesystem                = new Filesystem($adapter);
        $currentApplicationVersion = $this->version->getCurrentNumericVersion();
        $versionOfArchive          = $this->version->parseToNumericVersion($filesystem->read('VERSION'));

        // Wenn die Version NEUER ist als diese hier .... NIX DA!
        if (version_compare($versionOfArchive, $currentApplicationVersion, '>')) {
            throw new RuntimeException(
                'You can not import archives of newer version. The current version is "'
                . $currentApplicationVersion . '" and you try to import archive of version "' . $versionOfArchive . '"',
            );
        }

        if ($importSettings->pruneLibrary === true) {
            $this->logger->info('Executing pruning of data before import.');
            $this->eventDispatcher->dispatch(new ExecuteImportPruning($importSettings));
            $this->logger->info('Pruning of data was executed.');
        } else {
            $this->logger->debug('Skipping pruning of data before import.');
        }

        $importedFiles = new ImportedFileBag();
        foreach ($this->importer as $import) {
            $importedFiles = $importedFiles->extend(...$import->import($filesystem, $importSettings));
        }

        if ($importSettings->removeArchive === true) {
            @unlink($archiveFile);
        }

        $this->eventDispatcher->dispatch(new ImportFinished($importSettings, $importedFiles));

        foreach ($importedFiles as $importedFile) {
            $this->eventDispatcher->dispatch(new FileImported($importSettings, $importedFile, $versionOfArchive));
        }

        return $importedFiles;
    }
}
