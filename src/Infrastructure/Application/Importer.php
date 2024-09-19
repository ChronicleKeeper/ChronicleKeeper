<?php

declare(strict_types=1);

namespace DZunke\NovDoc\Infrastructure\Application;

use DZunke\NovDoc\Infrastructure\Application\Importer\ImportedFileBag;
use DZunke\NovDoc\Infrastructure\Application\Importer\SingleImport;
use DZunke\NovDoc\Infrastructure\Event\FileImported;
use League\Flysystem\Filesystem;
use League\Flysystem\ZipArchive\FilesystemZipArchiveProvider;
use League\Flysystem\ZipArchive\ZipArchiveAdapter;
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
        private readonly LibraryPruner $pruner,
        private readonly EventDispatcherInterface $eventDispatcher,
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
            $this->pruner->prune();
        }

        $importedFiles = new ImportedFileBag();
        foreach ($this->importer as $import) {
            $importedFiles = $importedFiles->extend(...$import->import($filesystem, $importSettings));
        }

        @unlink($archiveFile);

        foreach ($importedFiles as $importedFile) {
            $this->eventDispatcher->dispatch(new FileImported($importSettings, $importedFile, $versionOfArchive));
        }

        return $importedFiles;
    }
}
