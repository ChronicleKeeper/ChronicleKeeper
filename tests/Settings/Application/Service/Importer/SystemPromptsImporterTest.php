<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Settings\Application\Service\Importer;

use ChronicleKeeper\Settings\Application\Service\Importer\SystemPromptsImporter;
use ChronicleKeeper\Settings\Application\Service\ImportSettings;
use ChronicleKeeper\Shared\Infrastructure\Persistence\Filesystem\Contracts\FileAccess;
use ChronicleKeeper\Shared\Infrastructure\Persistence\Filesystem\PathRegistry;
use League\Flysystem\Filesystem;
use League\Flysystem\UnableToReadFile;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(SystemPromptsImporter::class)]
#[Small]
final class SystemPromptsImporterTest extends TestCase
{
    #[Test]
    public function itDoesImportNothingWhenOverwriteSettingsIsDisabled(): void
    {
        $pathRegistry = self::createStub(PathRegistry::class);
        $pathRegistry->method('get')->willReturn('storage');

        $fileAccess = $this->createMock(FileAccess::class);
        $fileAccess->expects($this->never())->method('write');

        $filesystem = $this->createMock(Filesystem::class);
        $filesystem->expects($this->never())->method('read');

        $importSettings = new ImportSettings(overwriteSettings: false);
        (new SystemPromptsImporter($fileAccess, $pathRegistry))->import(
            $filesystem,
            $importSettings,
        );
    }

    #[Test]
    public function itDoesImportNothingWhenFileNotInArchive(): void
    {
        $pathRegistry = self::createStub(PathRegistry::class);
        $pathRegistry->method('get')->willReturn('storage');

        $fileAccess = $this->createMock(FileAccess::class);
        $fileAccess->expects($this->never())->method('write');

        $filesystem = $this->createMock(Filesystem::class);
        $filesystem->method('read')->willThrowException(new UnableToReadFile());

        $importSettings = new ImportSettings(overwriteSettings: true);
        (new SystemPromptsImporter($fileAccess, $pathRegistry))->import(
            $filesystem,
            $importSettings,
        );
    }

    #[Test]
    public function itDoesImportFileWhenOverwriteSettingsIsEnabled(): void
    {
        $pathRegistry = self::createStub(PathRegistry::class);
        $pathRegistry->method('get')->willReturn('storage');

        $fileAccess = $this->createMock(FileAccess::class);
        $fileAccess->expects($this->once())
            ->method('write')
            ->with('storage', 'system_prompts.json', 'content');

        $filesystem = $this->createMock(Filesystem::class);
        $filesystem->method('read')->willReturn('content');

        $importSettings = new ImportSettings(overwriteSettings: true);
        (new SystemPromptsImporter($fileAccess, $pathRegistry))->import(
            $filesystem,
            $importSettings,
        );
    }
}
