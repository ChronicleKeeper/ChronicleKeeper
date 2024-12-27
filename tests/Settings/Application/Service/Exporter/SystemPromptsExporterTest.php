<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Settings\Application\Service\Exporter;

use ChronicleKeeper\Settings\Application\Service\Exporter\SystemPromptsExporter;
use ChronicleKeeper\Shared\Infrastructure\Persistence\Filesystem\Contracts\FileAccess;
use ChronicleKeeper\Shared\Infrastructure\Persistence\Filesystem\PathRegistry;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use ZipArchive;

#[CoversClass(SystemPromptsExporter::class)]
#[Small]
final class SystemPromptsExporterTest extends TestCase
{
    #[Test]
    public function itIsDoingNothingWhenFileNotExists(): void
    {
        $pathRegistry = $this->createMock(PathRegistry::class);
        $pathRegistry->expects($this->never())->method('get');

        $fileAccess = $this->createMock(FileAccess::class);
        $fileAccess->expects($this->once())->method('exists')->willReturn(false);

        $archive = $this->createMock(ZipArchive::class);
        $archive->expects($this->never())->method('addFile');

        $exporter = new SystemPromptsExporter($pathRegistry, $fileAccess);
        $exporter->export($archive);
    }

    #[Test]
    public function itIsStoringTheSystemPromptsFileToTheArchive(): void
    {
        $pathRegistry = $this->createMock(PathRegistry::class);
        $pathRegistry->expects($this->once())->method('get')->willReturn('storage');

        $fileAccess = $this->createMock(FileAccess::class);
        $fileAccess->expects($this->once())->method('exists')->willReturn(true);

        $archive = $this->createMock(ZipArchive::class);
        $archive->expects($this->once())
            ->method('addFile')
            ->with('storage/system_prompts.json', 'system_prompts.json');

        $exporter = new SystemPromptsExporter($pathRegistry, $fileAccess);
        $exporter->export($archive);
    }
}
