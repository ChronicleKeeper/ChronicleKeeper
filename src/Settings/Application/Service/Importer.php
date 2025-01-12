<?php

declare(strict_types=1);

namespace ChronicleKeeper\Settings\Application\Service;

use ChronicleKeeper\Settings\Application\Service\Importer\SingleImport;
use ChronicleKeeper\Settings\Domain\Event\ExecuteImportPruning;
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

    public function import(string $archiveFile, ImportSettings $importSettings): void
    {
        $adapter          = new ZipArchiveAdapter(new FilesystemZipArchiveProvider($archiveFile));
        $filesystem       = new Filesystem($adapter);
        $versionOfArchive = $this->version->parseToNumericVersion($filesystem->read('VERSION'));

        /**
         * If version is not 0.6 we can not import as we are in progress to migrate from filesystem to SQLite.
         * So the version 0.6 is required version to import from. This will also stop newer versions from being
         * imported.
         */
        if (! version_compare($versionOfArchive, '0.6', 'eq')) {
            throw new RuntimeException('Only import from version 0.6 is allowed for this version.');
        }

        if ($importSettings->pruneLibrary === true) {
            $this->logger->info('Executing pruning of data before import.');
            $this->eventDispatcher->dispatch(new ExecuteImportPruning($importSettings));
            $this->logger->info('Pruning of data was executed.');
        } else {
            $this->logger->debug('Skipping pruning of data before import.');
        }

        foreach ($this->importer as $import) {
            $this->logger->info('Executing import of type: ' . $import::class);
            $import->import($filesystem, $importSettings);
            $this->logger->info('Import of type: ' . $import::class . ' was executed');
        }

        if ($importSettings->removeArchive === true) {
            @unlink($archiveFile);
        }

        $this->eventDispatcher->dispatch(new ImportFinished($importSettings));
    }
}
