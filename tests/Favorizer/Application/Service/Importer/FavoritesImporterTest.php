<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Favorizer\Application\Service\Importer;

use ChronicleKeeper\Favorizer\Application\Service\Importer\FavoritesImporter;
use ChronicleKeeper\Settings\Application\Service\ImportSettings;
use ChronicleKeeper\Shared\Infrastructure\Persistence\Filesystem\Contracts\FileAccess;
use League\Flysystem\Filesystem;
use League\Flysystem\UnableToReadFile;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(FavoritesImporter::class)]
#[Small]
class FavoritesImporterTest extends TestCase
{
    #[Test]
    public function import(): void
    {
        $filesystem = $this->createMock(Filesystem::class);
        $filesystem->expects($this->once())
            ->method('read')
            ->with('favorites.json')
            ->willReturn('content');

        $fileAccess = $this->createMock(FileAccess::class);
        $fileAccess->expects($this->once())
            ->method('write')
            ->with('storage', 'favorites.json', 'content');

        $importer = new FavoritesImporter($fileAccess);
        $importer->import($filesystem, new ImportSettings());
    }

    #[Test]
    public function importWithFavoritesNotReadableFromArchive(): void
    {
        $filesystem = $this->createMock(Filesystem::class);
        $filesystem->expects($this->once())
            ->method('read')
            ->with('favorites.json')
            ->willThrowException(new UnableToReadFile('favorites.json'));

        $fileAccess = $this->createMock(FileAccess::class);
        $fileAccess->expects($this->never())
            ->method('write');

        $importer = new FavoritesImporter($fileAccess);
        $importer->import($filesystem, new ImportSettings());
    }
}
