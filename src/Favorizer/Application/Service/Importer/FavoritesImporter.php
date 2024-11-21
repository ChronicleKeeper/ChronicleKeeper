<?php

declare(strict_types=1);

namespace ChronicleKeeper\Favorizer\Application\Service\Importer;

use ChronicleKeeper\Settings\Application\Service\FileType;
use ChronicleKeeper\Settings\Application\Service\Importer\ImportedFile;
use ChronicleKeeper\Settings\Application\Service\Importer\ImportedFileBag;
use ChronicleKeeper\Settings\Application\Service\Importer\SingleImport;
use ChronicleKeeper\Settings\Application\Service\ImportSettings;
use ChronicleKeeper\Shared\Infrastructure\Persistence\Filesystem\Contracts\FileAccess;
use League\Flysystem\Filesystem;
use League\Flysystem\UnableToReadFile;

final readonly class FavoritesImporter implements SingleImport
{
    public function __construct(
        private FileAccess $fileAccess,
    ) {
    }

    public function import(Filesystem $filesystem, ImportSettings $settings): ImportedFileBag
    {
        try {
            $content = $filesystem->read('favorites.json');
        } catch (UnableToReadFile) {
            // It is totally fine, when the file is not available during import
            return new ImportedFileBag(ImportedFile::asIgnored('favorites.json', FileType::FAVORITES));
        }

        $this->fileAccess->write('storage', 'favorites.json', $content);

        return new ImportedFileBag(ImportedFile::asSuccess('favorites.json', FileType::FAVORITES));
    }
}
