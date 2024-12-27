<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Shared\Infrastructure\Persistence\Filesystem;

use ChronicleKeeper\Shared\Infrastructure\Persistence\Filesystem\Exception\UnableToDeleteFile;
use ChronicleKeeper\Shared\Infrastructure\Persistence\Filesystem\Exception\UnableToReadFile;
use ChronicleKeeper\Shared\Infrastructure\Persistence\Filesystem\Exception\UnableToWriteFile;
use ChronicleKeeper\Shared\Infrastructure\Persistence\Filesystem\FileAccess;
use ChronicleKeeper\Shared\Infrastructure\Persistence\Filesystem\PathRegistry;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Filesystem\Filesystem;

#[CoversClass(FileAccess::class)]
#[Small]
class FileAccessTest extends TestCase
{
    private FileAccess $fileAccess;
    private MockObject&PathRegistry $pathRegistry;
    private MockObject&Filesystem $filesystem;

    protected function setUp(): void
    {
        $this->pathRegistry = $this->createMock(PathRegistry::class);
        $this->filesystem   = $this->createMock(Filesystem::class);
        $this->fileAccess   = new FileAccess($this->pathRegistry, $this->filesystem);
    }

    #[Test]
    public function readFile(): void
    {
        $type     = 'documents';
        $filename = 'file.txt';
        $path     = '/var/www/data/documents/file.txt';
        $content  = 'file content';

        $this->pathRegistry->method('get')->with($type)->willReturn('/var/www/data/documents');
        $this->filesystem->method('exists')->with($path)->willReturn(true);
        $this->filesystem->method('readFile')->with($path)->willReturn($content);

        $result = $this->fileAccess->read($type, $filename);

        self::assertSame($content, $result);
    }

    #[Test]
    public function readFileThrowsException(): void
    {
        $type     = 'documents';
        $filename = 'file.txt';
        $path     = '/var/www/data/documents/file.txt';

        $this->pathRegistry->method('get')->with($type)->willReturn('/var/www/data/documents');
        $this->filesystem->method('exists')->with($path)->willReturn(false);

        $this->expectException(UnableToReadFile::class);

        $this->fileAccess->read($type, $filename);
    }

    #[Test]
    public function writeFile(): void
    {
        $type     = 'documents';
        $filename = 'file.txt';
        $path     = '/var/www/data/documents/file.txt';
        $content  = 'file content';

        $this->pathRegistry->method('get')->with($type)->willReturn('/var/www/data/documents');
        $this->filesystem->expects($this->once())->method('dumpFile')->with($path, $content);

        $this->fileAccess->write($type, $filename, $content);
    }

    #[Test]
    public function writeFileThrowsException(): void
    {
        $type     = 'documents';
        $filename = 'file.txt';
        $path     = '/var/www/data/documents/file.txt';
        $content  = 'file content';

        $this->pathRegistry->method('get')->with($type)->willReturn('/var/www/data/documents');
        $this->filesystem->method('dumpFile')->with($path, $content)->willThrowException(new IOException(''));

        $this->expectException(UnableToWriteFile::class);

        $this->fileAccess->write($type, $filename, $content);
    }

    #[Test]
    public function deleteFile(): void
    {
        $type     = 'documents';
        $filename = 'file.txt';
        $path     = '/var/www/data/documents/file.txt';

        $this->pathRegistry->method('get')->with($type)->willReturn('/var/www/data/documents');
        $this->filesystem->method('exists')->with($path)->willReturn(true);
        $this->filesystem->expects($this->once())->method('remove')->with($path);

        $this->fileAccess->delete($type, $filename);
    }

    #[Test]
    public function deleteFileThrowsException(): void
    {
        $type     = 'documents';
        $filename = 'file.txt';
        $path     = '/var/www/data/documents/file.txt';

        $this->pathRegistry->method('get')->with($type)->willReturn('/var/www/data/documents');
        $this->filesystem->method('exists')->with($path)->willReturn(true);
        $this->filesystem->method('remove')->with($path)->willThrowException(new IOException(''));

        $this->expectException(UnableToDeleteFile::class);

        $this->fileAccess->delete($type, $filename);
    }

    #[Test]
    public function thatAFileExists(): void
    {
        $type     = 'documents';
        $filename = 'file.txt';
        $path     = '/var/www/data/documents/file.txt';

        $this->pathRegistry->method('get')->with($type)->willReturn('/var/www/data/documents');
        $this->filesystem->method('exists')->with($path)->willReturn(true);

        $result = $this->fileAccess->exists($type, $filename);

        self::assertTrue($result);
    }

    #[Test]
    public function fileExistsReturnsFalse(): void
    {
        $type     = 'documents';
        $filename = 'file.txt';
        $path     = '/var/www/data/documents/file.txt';

        $this->pathRegistry->method('get')->with($type)->willReturn('/var/www/data/documents');
        $this->filesystem->method('exists')->with($path)->willReturn(false);

        $result = $this->fileAccess->exists($type, $filename);

        self::assertFalse($result);
    }

    #[Test]
    public function prune(): void
    {
        $type = 'documents';
        $path = '/var/www/data/documents';

        $this->pathRegistry->expects($this->once())
            ->method('get')
            ->with($type)
            ->willReturn('/var/www/data/documents');

        $this->filesystem
            ->expects($this->once())
            ->method('exists')
            ->with($path)
            ->willReturn(true);

        $this->filesystem
            ->expects($this->once())
            ->method('remove')
            ->with($path);

        $this->fileAccess->prune($type);
    }

    #[Test]
    public function pruneWhenDirectoryNotExists(): void
    {
        $type = 'documents';
        $path = '/var/www/data/documents';

        $this->pathRegistry
            ->expects($this->once())
            ->method('get')
            ->with($type)
            ->willReturn('/var/www/data/documents');

        $this->filesystem
            ->method('exists')
            ->with($path)
            ->willReturn(false);

        $this->filesystem->expects($this->never())->method('remove');

        $this->fileAccess->prune($type);
    }
}
