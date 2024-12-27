<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Shared\Infrastructure\Persistence\Filesystem;

use ChronicleKeeper\Shared\Infrastructure\Persistence\Filesystem\CachedFileAccess;
use ChronicleKeeper\Shared\Infrastructure\Persistence\Filesystem\FileAccess;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(CachedFileAccess::class)]
#[Small]
final class CachedFileAccessTest extends TestCase
{
    #[Test]
    public function itReadsACachedEntry(): void
    {
        $fileAccess = $this->createMock(FileAccess::class);
        $fileAccess->expects($this->once())
            ->method('read')
            ->with('type', 'filename')
            ->willReturn('content');

        $cachedFileAccess = new CachedFileAccess($fileAccess);

        self::assertSame('content', $cachedFileAccess->read('type', 'filename'));
        self::assertSame('content', $cachedFileAccess->read('type', 'filename'));
    }

    #[Test]
    public function itWritesACachedEntry(): void
    {
        $fileAccess = $this->createMock(FileAccess::class);
        $fileAccess->expects($this->never())->method('read');
        $fileAccess->expects($this->once())
            ->method('write')
            ->with('type', 'filename', 'content');

        $cachedFileAccess = new CachedFileAccess($fileAccess);

        $cachedFileAccess->write('type', 'filename', 'content');
        $cachedFileAccess->read('type', 'filename');
    }

    #[Test]
    public function itDeletesACachedEntry(): void
    {
        $fileAccess = $this->createMock(FileAccess::class);
        $fileAccess->expects($this->never())->method('read');
        $fileAccess->expects($this->once())->method('exists')->willReturn(false);
        $fileAccess->expects($this->once())
            ->method('delete')
            ->with('type', 'filename');

        $cachedFileAccess = new CachedFileAccess($fileAccess);

        // Write file and ensure it is cached
        $cachedFileAccess->write('type', 'filename', 'content');
        self::assertTrue($cachedFileAccess->exists('type', 'filename'));

        // Delete file and ensure it is no longer cached
        $cachedFileAccess->delete('type', 'filename');
        self::assertFalse($cachedFileAccess->exists('type', 'filename'));
    }

    #[Test]
    public function itPrunesCachedEntries(): void
    {
        $fileAccess = $this->createMock(FileAccess::class);
        $fileAccess->expects($this->never())->method('read');
        $fileAccess->expects($this->once())->method('prune')->with('type');

        $cachedFileAccess = new CachedFileAccess($fileAccess);

        // Write file and ensure it is cached
        $cachedFileAccess->write('type', 'filename', 'content');
        self::assertTrue($cachedFileAccess->exists('type', 'filename'));

        // Prune files and ensure they are no longer cached
        $cachedFileAccess->prune('type');
        self::assertFalse($cachedFileAccess->exists('type', 'filename'));
    }
}
