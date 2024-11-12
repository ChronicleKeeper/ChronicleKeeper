<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Favorizer\Application\Service\Exporter;

use ChronicleKeeper\Favorizer\Application\Service\Exporter\FavoritesExport;
use ChronicleKeeper\Shared\Infrastructure\Persistence\Filesystem\PathRegistry;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use ZipArchive;

use const DIRECTORY_SEPARATOR;

#[CoversClass(FavoritesExport::class)]
#[Small]
class FavoritesExportTest extends TestCase
{
    #[Test]
    public function testExport(): void
    {
        $pathRegistry = $this->createMock(PathRegistry::class);
        $pathRegistry->method('get')->with('storage')->willReturn('MyStorage');

        $archive = $this->createMock(ZipArchive::class);
        $archive->expects($this->once())
            ->method('addFile')
            ->with(
                'MyStorage' . DIRECTORY_SEPARATOR . 'favorites.json',
                'favorites.json',
            );

        $exporter = new FavoritesExport($pathRegistry);
        $exporter->export($archive);
    }
}
