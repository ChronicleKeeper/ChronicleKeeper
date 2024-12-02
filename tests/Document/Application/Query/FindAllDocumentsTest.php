<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Document\Application\Query;

use ChronicleKeeper\Document\Application\Query\FindAllDocuments;
use ChronicleKeeper\Document\Application\Query\FindAllDocumentsQuery;
use ChronicleKeeper\Library\Domain\Entity\Directory;
use ChronicleKeeper\Library\Domain\Entity\Document;
use ChronicleKeeper\Shared\Infrastructure\Persistence\Filesystem\Contracts\FileAccess;
use ChronicleKeeper\Shared\Infrastructure\Persistence\Filesystem\Contracts\Finder;
use ChronicleKeeper\Shared\Infrastructure\Persistence\Filesystem\PathRegistry;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use RuntimeException;
use SplFileInfo;
use Symfony\Component\Serializer\SerializerInterface;
use UnexpectedValueException;

#[CoversClass(FindAllDocuments::class)]
#[CoversClass(FindAllDocumentsQuery::class)]
#[Small]
class FindAllDocumentsTest extends TestCase
{
    #[Test]
    public function parametersAreInitializable(): void
    {
        $parameters = new FindAllDocuments();

        self::assertSame(FindAllDocumentsQuery::class, $parameters->getQueryClass());
    }

    #[Test]
    public function queryWorkingWithoutResults(): void
    {
        $pathRegistry = $this->createMock(PathRegistry::class);
        $pathRegistry->expects($this->once())
            ->method('get')
            ->with('library.documents')
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

        $query = new FindAllDocumentsQuery(
            $pathRegistry,
            $finder,
            $fileAccess,
            $serializer,
            $logger,
        );

        $documents = $query->query(new FindAllDocuments());

        self::assertSame([], $documents);
    }

    #[Test]
    public function queryWithSortedResults(): void
    {
        $fileOne = $this->createMock(SplFileInfo::class);
        $fileOne->expects($this->once())->method('getFilename')->willReturn('foo.json');

        $fileTwo = $this->createMock(SplFileInfo::class);
        $fileTwo->expects($this->once())->method('getFilename')->willReturn('bar.json');

        $pathRegistry = $this->createMock(PathRegistry::class);
        $pathRegistry->expects($this->once())
            ->method('get')
            ->with('library.documents')
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

        $serializer = $this->createMock(SerializerInterface::class);
        $serializer->expects($this->exactly(2))
            ->method('deserialize')
            ->willReturnCallback(
                static function (string $content, string $class): object {
                    self::assertSame(Document::class, $class);

                    $directory     = new Directory('foo.directory');
                    $directory->id = 'foo';

                    if ($content === 'foo.content') {
                        $document            = new Document('foo', 'foo');
                        $document->directory = $directory;

                        return $document;
                    }

                    if ($content === 'bar.content') {
                        $document            = new Document('bar', 'bar');
                        $document->directory = $directory;

                        return $document;
                    }

                    throw new UnexpectedValueException('Unexpected content');
                },
            );

        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects($this->never())->method('error');

        $query = new FindAllDocumentsQuery(
            $pathRegistry,
            $finder,
            $fileAccess,
            $serializer,
            $logger,
        );

        $documents = $query->query(new FindAllDocuments());

        self::assertCount(2, $documents);
        self::assertSame('bar', $documents[0]->title);
        self::assertSame('foo', $documents[1]->title);
    }

    #[Test]
    public function queryWithLoggingAndIgnoringExceptionsDuringDeserialization(): void
    {
        $fileOne = $this->createMock(SplFileInfo::class);
        $fileOne->expects($this->once())->method('getFilename')->willReturn('foo.json');

        $pathRegistry = $this->createMock(PathRegistry::class);
        $pathRegistry->expects($this->once())
            ->method('get')
            ->with('library.documents')
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

        $query = new FindAllDocumentsQuery(
            $pathRegistry,
            $finder,
            $fileAccess,
            $serializer,
            $logger,
        );

        $documents = $query->query(new FindAllDocuments());

        self::assertCount(0, $documents);
    }
}
