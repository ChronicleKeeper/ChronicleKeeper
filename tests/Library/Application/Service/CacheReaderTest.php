<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Library\Application\Service;

use ChronicleKeeper\Library\Application\Service\CacheBuilder;
use ChronicleKeeper\Library\Application\Service\CacheReader;
use ChronicleKeeper\Library\Domain\ValueObject\DirectoryCache\Directory as DirectoryCache;
use ChronicleKeeper\Shared\Infrastructure\Persistence\Filesystem\Contracts\FileAccess;
use ChronicleKeeper\Test\Library\Domain\Entity\DirectoryBuilder;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\SerializerInterface;

#[CoversClass(CacheReader::class)]
#[Small]
class CacheReaderTest extends TestCase
{
    private FileAccess&MockObject $fileAccess;
    private CacheBuilder&MockObject $cacheBuilder;
    private SerializerInterface&MockObject $serializer;
    private CacheReader $cacheReader;

    protected function setUp(): void
    {
        $this->fileAccess   = $this->createMock(FileAccess::class);
        $this->cacheBuilder = $this->createMock(CacheBuilder::class);
        $this->serializer   = $this->createMock(SerializerInterface::class);
        $this->cacheReader  = new CacheReader($this->fileAccess, $this->cacheBuilder, $this->serializer);
    }

    protected function tearDown(): void
    {
        unset($this->fileAccess, $this->cacheBuilder, $this->serializer, $this->cacheReader);
    }

    #[Test]
    public function readCacheExists(): void
    {
        $directory = (new DirectoryBuilder())->build();

        $filename       = $directory->id . '.json';
        $cacheData      = '{"id": "' . $directory->id . '","title":"Test Directory","elements":[],"directories":[]}';
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
        $filename       = $directory->id . '.json';
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
}
