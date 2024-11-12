<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Settings\Application\Service\Importer;

use ChronicleKeeper\Settings\Application\Service\FileType;
use ChronicleKeeper\Settings\Application\Service\Importer\DotEnvImporter;
use ChronicleKeeper\Settings\Application\Service\Importer\ImportedFile;
use ChronicleKeeper\Settings\Application\Service\ImportSettings;
use ChronicleKeeper\Shared\Infrastructure\Persistence\Filesystem\Contracts\FileAccess;
use League\Flysystem\Filesystem;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(DotEnvImporter::class)]
#[Small]
class DotEnvImporterTest extends TestCase
{
    #[Test]
    public function testImport(): void
    {
        $filesystem = $this->createMock(Filesystem::class);
        $filesystem->expects($this->once())->method('read')->with('.env')->willReturn('APP_DEBUG=0');

        $fileAccess = $this->createMock(FileAccess::class);
        $fileAccess->expects($this->once())->method('write')->with('general.project', '.env', 'APP_DEBUG=0');

        $dotEnvImporter  = new DotEnvImporter($fileAccess);
        $importedFileBag = $dotEnvImporter->import($filesystem, new ImportSettings());

        self::assertCount(1, $importedFileBag);

        self::assertInstanceOf(ImportedFile::class, $importedFileBag[0]);
        self::assertSame('.env', $importedFileBag[0]->file);
        self::assertSame(FileType::DOTENV, $importedFileBag[0]->type);
    }
}
