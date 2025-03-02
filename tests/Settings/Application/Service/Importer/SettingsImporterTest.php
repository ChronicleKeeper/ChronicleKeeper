<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Settings\Application\Service\Importer;

use ChronicleKeeper\Settings\Application\Service\Importer\SettingsImporter;
use ChronicleKeeper\Settings\Application\Service\ImportSettings;
use ChronicleKeeper\Shared\Infrastructure\Persistence\Filesystem\Contracts\FileAccess;
use League\Flysystem\Filesystem;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

#[CoversClass(SettingsImporter::class)]
#[Small]
final class SettingsImporterTest extends TestCase
{
    private LoggerInterface&MockObject $logger;
    private FileAccess&MockObject $fileAccess;

    protected function setUp(): void
    {
        $this->logger     = $this->createMock(LoggerInterface::class);
        $this->fileAccess = $this->createMock(FileAccess::class);
    }

    protected function tearDown(): void
    {
        unset($this->logger, $this->fileAccess);
    }

    #[Test]
    public function itIsNotImportingSomethingOnExistingFileAndDisallowedOverwrite(): void
    {
        // ------------- Setup Expectations -------------
        $this->logger
            ->expects($this->once())
            ->method('info')
            ->with('Settings import skipped, as settings already exist and overwrite is disabled.');

        $this->fileAccess
            ->expects($this->once())
            ->method('exists')
            ->with('storage', 'settings.json')
            ->willReturn(true);

        $this->fileAccess->expects($this->never())->method('write');

        $filesystem = $this->createMock(Filesystem::class);
        $filesystem->expects($this->never())->method('read');

        $importSettings = new ImportSettings(false, false);

        // ------------- Execute Tests -------------

        $importer = new SettingsImporter($this->fileAccess, $this->logger);
        $importer->import($filesystem, $importSettings);

        // No Assertions as those are done with expectations
    }

    #[Test]
    public function itIsImportingSettings(): void
    {
        // ------------- Setup Expectations -------------

        $this->logger->expects($this->never())->method('info');
        $this->fileAccess->expects($this->never())->method('exists');

        $filesystem = $this->createMock(Filesystem::class);
        $filesystem
            ->expects($this->once())
            ->method('read')
            ->with('settings.json')
            ->willReturn('{"setting": true}');

        $this->fileAccess
            ->expects($this->once())
            ->method('write')
            ->with('storage', 'settings.json', '{"setting":true}');

        $importSettings = new ImportSettings(true, false);

        // ------------- Execute Tests -------------

        $importer = new SettingsImporter($this->fileAccess, $this->logger);
        $importer->import($filesystem, $importSettings);

        // No Assertions as those are done with expectations
    }

    #[Test]
    public function itIsRemovingOldCalendarSettingsOnImport(): void
    {
// ------------- Setup Expectations -------------

        $this->logger->expects($this->never())->method('info');
        $this->fileAccess->expects($this->never())->method('exists');

        $filesystem = $this->createMock(Filesystem::class);
        $filesystem
            ->expects($this->once())
            ->method('read')
            ->with('settings.json')
            ->willReturn('{"setting": true, "calendar": false}');

        $this->fileAccess
            ->expects($this->once())
            ->method('write')
            ->with('storage', 'settings.json', '{"setting":true}');

        $importSettings = new ImportSettings(true, false);

        // ------------- Execute Tests -------------

        $importer = new SettingsImporter($this->fileAccess, $this->logger);
        $importer->import($filesystem, $importSettings);

        // No Assertions as those are done with expectations
    }
}
