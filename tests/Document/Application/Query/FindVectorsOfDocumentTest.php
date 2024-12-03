<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Document\Application\Query;

use ChronicleKeeper\Document\Application\Query\FindVectorsOfDocument;
use ChronicleKeeper\Document\Application\Query\FindVectorsOfDocumentQuery;
use ChronicleKeeper\Library\Infrastructure\VectorStorage\VectorDocument;
use ChronicleKeeper\Shared\Infrastructure\Persistence\Filesystem\Contracts\FileAccess;
use ChronicleKeeper\Shared\Infrastructure\Persistence\Filesystem\Contracts\Finder;
use ChronicleKeeper\Shared\Infrastructure\Persistence\Filesystem\Exception\UnableToReadFile;
use ChronicleKeeper\Shared\Infrastructure\Persistence\Filesystem\PathRegistry;
use ChronicleKeeper\Test\Library\Domain\Entity\DocumentBuilder;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use RuntimeException;
use SplFileInfo;
use Symfony\Component\Serializer\SerializerInterface;
use UnexpectedValueException;

#[CoversClass(FindVectorsOfDocument::class)]
#[CoversClass(FindVectorsOfDocumentQuery::class)]
#[Small]
class FindVectorsOfDocumentTest extends TestCase
{
    #[Test]
    public function parametersAreInitializable(): void
    {
        $parameters = new FindVectorsOfDocument('foo');

        self::assertSame('foo', $parameters->id);
        self::assertSame(FindVectorsOfDocumentQuery::class, $parameters->getQueryClass());
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

        $query = new FindVectorsOfDocumentQuery(
            $pathRegistry,
            $finder,
            $fileAccess,
            $serializer,
            $logger,
        );

        $documents = $query->query(new FindVectorsOfDocument('foo'));

        self::assertSame([], $documents);
    }

    #[Test]
    public function queryWithFilteredResults(): void
    {
        $fileOne = $this->createMock(SplFileInfo::class);
        $fileOne->expects($this->once())->method('getFilename')->willReturn('foo.json');

        $fileTwo = $this->createMock(SplFileInfo::class);
        $fileTwo->expects($this->once())->method('getFilename')->willReturn('bar.json');

        $pathRegistry = $this->createMock(PathRegistry::class);
        $pathRegistry->expects($this->once())
            ->method('get')
            ->with('vector.documents')
            ->willReturn('foo');

        $finder = $this->createMock(Finder::class);
        $finder->expects($this->once())
            ->method('findFilesInDirectory')
            ->with('foo')
            ->willReturn([$fileOne, $fileTwo]);

        $fileAccess = $this->createMock(FileAccess::class);
        $fileAccess->expects($this->exactly(2))
            ->method('read')
            ->willReturnCallback(
                static fn (string $storage, string $filename): string => match ($filename) {
                    'foo.json' => 'foo.content',
                    'bar.json' => 'bar.content',
                    default => '',
                },
            );

        $fooDocument = (new DocumentBuilder())->build();

        $serializer = $this->createMock(SerializerInterface::class);
        $serializer->expects($this->exactly(2))
            ->method('deserialize')
            ->willReturnCallback(
                static function (string $content, string $class) use ($fooDocument): object {
                    self::assertSame(VectorDocument::class, $class);

                    if ($content === 'foo.content') {
                        return new VectorDocument($fooDocument, 'foo', 'foo', []);
                    }

                    if ($content === 'bar.content') {
                        $document = (new DocumentBuilder())->build();

                        return new VectorDocument($document, 'bar', 'bar', []);
                    }

                    throw new UnexpectedValueException('Unexpected content');
                },
            );

        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects($this->never())->method('error');

        $query = new FindVectorsOfDocumentQuery(
            $pathRegistry,
            $finder,
            $fileAccess,
            $serializer,
            $logger,
        );

        $documents = $query->query(new FindVectorsOfDocument($fooDocument->id));

        self::assertCount(1, $documents);
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

        $query = new FindVectorsOfDocumentQuery(
            $pathRegistry,
            $finder,
            $fileAccess,
            $serializer,
            $logger,
        );

        $documents = $query->query(new FindVectorsOfDocument('foo'));

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

        $query = new FindVectorsOfDocumentQuery(
            $pathRegistry,
            $finder,
            $fileAccess,
            $serializer,
            $logger,
        );

        $documents = $query->query(new FindVectorsOfDocument('foo'));

        self::assertCount(0, $documents);
    }
}
