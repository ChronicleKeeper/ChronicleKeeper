<?php

declare(strict_types=1);

namespace ChronicleKeeper\Favorizer\Application\Service\Importer;

use ChronicleKeeper\Settings\Application\Service\FileType;
use ChronicleKeeper\Settings\Application\Service\Importer\ImportedFile;
use ChronicleKeeper\Settings\Application\Service\Importer\ImportedFileBag;
use ChronicleKeeper\Settings\Application\Service\Importer\SingleImport;
use ChronicleKeeper\Settings\Application\Service\ImportSettings;
use ChronicleKeeper\Shared\Infrastructure\Database\DatabasePlatform;
use League\Flysystem\Filesystem;
use League\Flysystem\UnableToReadFile;

use function json_decode;

use const JSON_THROW_ON_ERROR;

final readonly class FavoritesImporter implements SingleImport
{
    public function __construct(
        private DatabasePlatform $databasePlatform,
    ) {
    }

    public function import(Filesystem $filesystem, ImportSettings $settings): ImportedFileBag
    {
        if ($settings->pruneLibrary === true) {
            // As the library was pruned this has to be cleared as well
            $this->databasePlatform->truncateTable('favorites');
        }

        try {
            $content = $filesystem->read('favorites.json');
        } catch (UnableToReadFile) {
            // It is totally fine, when the file is not available during import
            return new ImportedFileBag(ImportedFile::asIgnored('favorites.json', FileType::FAVORITES));
        }

        $content = json_decode($content, true, 512, JSON_THROW_ON_ERROR);
        foreach ($content as $row) {
            $this->databasePlatform->insertOrUpdate(
                'favorites',
                [
                    'id' => $row['id'],
                    'type' => $row['type'],
                    'title' => $row['title'],
                ],
            );
        }

        return new ImportedFileBag(ImportedFile::asSuccess('favorites.json', FileType::FAVORITES));
    }
}
