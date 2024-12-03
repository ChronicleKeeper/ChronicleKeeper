<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Document\Application\Query;

use ChronicleKeeper\Document\Application\Query\FindAllDocumentVectors;
use ChronicleKeeper\Document\Application\Query\FindAllDocumentVectorsQuery;
use ChronicleKeeper\Shared\Infrastructure\Persistence\Filesystem\Contracts\FileAccess;
use ChronicleKeeper\Shared\Infrastructure\Persistence\Filesystem\Contracts\Finder;
use ChronicleKeeper\Shared\Infrastructure\Persistence\Filesystem\Exception\UnableToReadFile;
use ChronicleKeeper\Shared\Infrastructure\Persistence\Filesystem\PathRegistry;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use RuntimeException;
use SplFileInfo;
use Symfony\Component\Serializer\SerializerInterface;

#[CoversClass(FindAllDocumentVectors::class)]
#[CoversClass(FindAllDocumentVectorsQuery::class)]
#[Small]
class FindAllDocumentVectorsTest extends TestCase
{
    #[Test]
    public function parametersAreInitializable(): void
    {
        $parameters = new FindAllDocumentVectors();

        self::assertSame(FindAllDocumentVectorsQuery::class, $parameters->getQueryClass());
    }

    #[Test]
    public function queryWorkingWithoutResults(): void
    {
        $pathRegistry = $this->createMock(PathRegistry::class);
        $pathRegistry->expects($this->once())
            ->method('get')
            ->with('vector.documents')
            ->willReturn('foo');

        $finder = $this->createMock(Finder::class);
        $finder->expects($this->once())
            ->method('findFilesInDirectory')
            ->with('foo')
            ->willReturn([]);

        $fileAccess = $this->createMock(FileAccess::class);
        $fileAccess->expects($this->never())->method('read');

        $serializer = $this->createMock(SerializerInterface::class);
        $serializer->expects($this->never())->method('deserialize');

        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects($this->never())->method('error');

        $query = new FindAllDocumentVectorsQuery(
            $pathRegistry,
            $finder,
            $fileAccess,
            $serializer,
            $logger,
        );

        $documents = $query->query(new FindAllDocumentVectors());

        self::assertSame([], $documents);
    }

    #[Test]
    public function queryWithLoggingAndIgnoringExceptionsDuringDeserialization(): void
    {
        $fileOne = $this->createMock(SplFileInfo::class);
        $fileOne->expects($this->once())->method('getFilename')->willReturn('foo.json');

        $pathRegistry = $this->createMock(PathRegistry::class);
        $pathRegistry->expects($this->once())
            ->method('get')
            ->with('vector.documents')
            ->willReturn('foo');

        $finder = $this->createMock(Finder::class);
        $finder->expects($this->once())
            ->method('findFilesInDirectory')
            ->with('foo')
            ->willReturn([$fileOne]);

        $fileAccess = $this->createMock(FileAccess::class);
        $fileAccess->expects($this->once())
            ->method('read')
            ->willReturnCallback(
                static fn (string $storage, string $filename): string => match ($filename) {
                    'foo.json' => 'foo.content',
                    default => '',
                },
            );

        $serializer = $this->createMock(SerializerInterface::class);
        $serializer->expects($this->once())
            ->method('deserialize')
            ->willThrowException(new RuntimeException('Deserialization failed'));

        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects($this->once())
            ->method('error')
            ->with(self::isInstanceOf(RuntimeException::class), ['file' => $fileOne]);

        $query = new FindAllDocumentVectorsQuery(
            $pathRegistry,
            $finder,
            $fileAccess,
            $serializer,
            $logger,
        );

        $documents = $query->query(new FindAllDocumentVectors());

        self::assertCount(0, $documents);
    }

    #[Test]
    public function queryWithLoggingAndIgnoringExceptionsDuringFileRead(): void
    {
        $fileOne = $this->createMock(SplFileInfo::class);
        $fileOne->expects($this->once())->method('getFilename')->willReturn('foo.json');

        $pathRegistry = $this->createMock(PathRegistry::class);
        $pathRegistry->expects($this->once())
            ->method('get')
            ->with('vector.documents')
            ->willReturn('foo');

        $finder = $this->createMock(Finder::class);
        $finder->expects($this->once())
            ->method('findFilesInDirectory')
            ->with('foo')
            ->willReturn([$fileOne]);

        $fileAccess = $this->createMock(FileAccess::class);
        $fileAccess->expects($this->once())
            ->method('read')
            ->willThrowException(new UnableToReadFile('foo.json'));

        $serializer = $this->createMock(SerializerInterface::class);
        $serializer->expects($this->never())
            ->method('deserialize');

        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects($this->once())
            ->method('error')
            ->with(self::isInstanceOf(UnableToReadFile::class), ['file' => $fileOne]);

        $query = new FindAllDocumentVectorsQuery(
            $pathRegistry,
            $finder,
            $fileAccess,
            $serializer,
            $logger,
        );

        $documents = $query->query(new FindAllDocumentVectors());

        self::assertCount(0, $documents);
    }
}
