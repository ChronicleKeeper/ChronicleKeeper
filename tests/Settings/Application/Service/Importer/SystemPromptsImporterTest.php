<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Settings\Application\Service\Importer;

use ChronicleKeeper\Settings\Application\Service\FileType;
use ChronicleKeeper\Settings\Application\Service\Importer\ImportedFile;
use ChronicleKeeper\Settings\Application\Service\Importer\State;
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
        $importResult   = (new SystemPromptsImporter($fileAccess, $pathRegistry))->import(
            $filesystem,
            $importSettings,
        );

        self::assertCount(1, $importResult->getArrayCopy());
        self::assertInstanceOf(ImportedFile::class, $importResult[0]);
        self::assertSame('storage/system_prompts.json', $importResult[0]->file);
        self::assertSame(FileType::SYSTEM_PROMPTS, $importResult[0]->type);
        self::assertSame(State::IGNORED, $importResult[0]->state);
        self::assertSame('Settings overwrite is disabled.', $importResult[0]->comment);
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
        $importResult   = (new SystemPromptsImporter($fileAccess, $pathRegistry))->import(
            $filesystem,
            $importSettings,
        );

        self::assertCount(1, $importResult->getArrayCopy());
        self::assertInstanceOf(ImportedFile::class, $importResult[0]);
        self::assertSame('storage/system_prompts.json', $importResult[0]->file);
        self::assertSame(FileType::SYSTEM_PROMPTS, $importResult[0]->type);
        self::assertSame(State::IGNORED, $importResult[0]->state);
        self::assertSame('File not in archive.', $importResult[0]->comment);
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
        $importResult   = (new SystemPromptsImporter($fileAccess, $pathRegistry))->import(
            $filesystem,
            $importSettings,
        );

        self::assertCount(1, $importResult->getArrayCopy());
        self::assertInstanceOf(ImportedFile::class, $importResult[0]);
        self::assertSame('storage/system_prompts.json', $importResult[0]->file);
        self::assertSame(FileType::SYSTEM_PROMPTS, $importResult[0]->type);
        self::assertSame(State::SUCCESS, $importResult[0]->state);
        self::assertSame('', $importResult[0]->comment);
    }
}
