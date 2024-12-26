<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Library\Application\Service;

use ChronicleKeeper\Library\Application\Service\CacheBuilder;
use ChronicleKeeper\Library\Application\Service\CacheReader;
use ChronicleKeeper\Library\Domain\ValueObject\DirectoryCache\Directory as DirectoryCache;
use ChronicleKeeper\Shared\Infrastructure\Persistence\Filesystem\Contracts\FileAccess;
use ChronicleKeeper\Shared\Infrastructure\Persistence\Filesystem\Contracts\Finder;
use ChronicleKeeper\Shared\Infrastructure\Persistence\Filesystem\PathRegistry;
use ChronicleKeeper\Test\Library\Domain\Entity\DirectoryBuilder;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use SplFileInfo;
use Symfony\Component\Serializer\SerializerInterface;

#[CoversClass(CacheReader::class)]
#[Small]
class CacheReaderTest extends TestCase
{
    private FileAccess&MockObject $fileAccess;
    private CacheBuilder&MockObject $cacheBuilder;
    private SerializerInterface&MockObject $serializer;
    private Finder&MockObject $finder;
    private PathRegistry&MockObject $pathRegistry;
    private CacheReader $cacheReader;

    protected function setUp(): void
    {
        $this->fileAccess   = $this->createMock(FileAccess::class);
        $this->cacheBuilder = $this->createMock(CacheBuilder::class);
        $this->serializer   = $this->createMock(SerializerInterface::class);
        $this->finder       = $this->createMock(Finder::class);
        $this->pathRegistry = $this->createMock(PathRegistry::class);

        $this->cacheReader = new CacheReader(
            $this->fileAccess,
            $this->cacheBuilder,
            $this->serializer,
            $this->finder,
            $this->pathRegistry,
        );
    }

    protected function tearDown(): void
    {
        unset(
            $this->fileAccess,
            $this->cacheBuilder,
            $this->serializer,
            $this->cacheReader,
            $this->finder,
            $this->pathRegistry,
        );
    }

    #[Test]
    public function readCacheExists(): void
    {
        $directory = (new DirectoryBuilder())->build();

        $filename       = $directory->getId() . '.json';
        $cacheData      = '{"id": "' . $directory->getId() . '","title":"Test Directory","elements":[],"directories":[]}';
        $directoryCache = new DirectoryCache('test-id', 'Test Directory', [], []);

        $this->fileAccess->expects($this->once())
            ->method('exists')
            ->with('library.directories.cache', $filename)
            ->willReturn(true);

        $this->fileAccess->expects($this->once())
            ->method('read')
            ->with('library.directories.cache', $filename)
            ->willReturn($cacheData);

        $this->serializer->expects($this->once())
            ->method('deserialize')
            ->with($cacheData, DirectoryCache::class, 'json')
            ->willReturn($directoryCache);

        $result = $this->cacheReader->read($directory);

        self::assertEquals($directoryCache, $result);
    }

    #[Test]
    public function readCacheDoesNotExist(): void
    {
        $directory      = (new DirectoryBuilder())->build();
        $filename       = $directory->getId() . '.json';
        $directoryCache = new DirectoryCache('test-id', 'Test Directory', [], []);

        $this->fileAccess->expects($this->once())
            ->method('exists')
            ->with('library.directories.cache', $filename)
            ->willReturn(false);

        $this->cacheBuilder->expects($this->once())
            ->method('build')
            ->with($directory)
            ->willReturn($directoryCache);

        $this->serializer->expects($this->once())
            ->method('serialize')
            ->with($directoryCache, 'json')
            ->willReturn('{"id":"test-id","title":"Test Directory","elements":[],"directories":[]}');

        $this->fileAccess->expects($this->once())
            ->method('write')
            ->with(
                'library.directories.cache',
                $filename,
                '{"id":"test-id","title":"Test Directory","elements":[],"directories":[]}',
            );

        $result = $this->cacheReader->read($directory);

        self::assertEquals($directoryCache, $result);
    }

    #[Test]
    public function refresh(): void
    {
        $directory      = (new DirectoryBuilder())->build();
        $filename       = $directory->getId() . '.json';
        $directoryCache = new DirectoryCache('test-id', 'Test Directory', [], []);

        $this->cacheBuilder->expects($this->once())
            ->method('build')
            ->with($directory)
            ->willReturn($directoryCache);

        $this->serializer->expects($this->once())
            ->method('serialize')
            ->with($directoryCache, 'json')
            ->willReturn('{"id":"test-id","title":"Test Directory","elements":[],"directories":[]}');

        $this->fileAccess->expects($this->once())
            ->method('write')
            ->with(
                'library.directories.cache',
                $filename,
                '{"id":"test-id","title":"Test Directory","elements":[],"directories":[]}',
            );

        $result = $this->cacheReader->refresh($directory);

        self::assertEquals($directoryCache, $result);
    }

    #[Test]
    public function remove(): void
    {
        $directory = (new DirectoryBuilder())->build();
        $filename  = $directory->getId() . '.json';

        $this->fileAccess->expects($this->once())
            ->method('delete')
            ->with('library.directories.cache', $filename);

        $this->cacheReader->remove($directory);
    }

    #[Test]
    public function clear(): void
    {
        $this->pathRegistry->expects($this->once())
            ->method('get')
            ->with('library.directories.cache')
            ->willReturn('/path/to/cache');

        $this->finder->expects($this->once())
            ->method('findFilesInDirectory')
            ->with('/path/to/cache')
            ->willReturn([
                new SplFileInfo('/path/to/cache/test-1.json'),
                new SplFileInfo('/path/to/cache/test-2.json'),
            ]);

        $invoker = $this->exactly(2);
        $this->fileAccess->expects($invoker)
            ->method('delete')
            ->willReturnCallback(
                static function (string $path, string $filename) use ($invoker): void {
                    self::assertSame('library.directories.cache', $path);

                    if ($invoker->numberOfInvocations() === 1) {
                        self::assertSame('test-1.json', $filename);
                    } else {
                        self::assertSame('test-2.json', $filename);
                    }
                },
            );

        $this->cacheReader->clear();
    }
}
