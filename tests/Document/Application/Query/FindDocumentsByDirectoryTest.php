<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Document\Application\Query;

use ChronicleKeeper\Document\Application\Query\FindDocumentsByDirectory;
use ChronicleKeeper\Document\Application\Query\FindDocumentsByDirectoryQuery;
use ChronicleKeeper\Document\Domain\Entity\Document;
use ChronicleKeeper\Test\Document\Domain\Entity\DocumentBuilder;
use ChronicleKeeper\Test\Library\Domain\Entity\DirectoryBuilder;
use ChronicleKeeper\Test\Shared\Infrastructure\Database\DatabasePlatformMock;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use UnexpectedValueException;

#[CoversClass(FindDocumentsByDirectory::class)]
#[CoversClass(FindDocumentsByDirectoryQuery::class)]
#[Small]
class FindDocumentsByDirectoryTest extends TestCase
{
    #[Test]
    public function parametersAreInitializable(): void
    {
        $parameters = new FindDocumentsByDirectory('foo');

        self::assertSame('foo', $parameters->id);
        self::assertSame(FindDocumentsByDirectoryQuery::class, $parameters->getQueryClass());
    }

    #[Test]
    public function queryWorkingWithoutResults(): void
    {
        $denormalizer = $this->createMock(DenormalizerInterface::class);
        $denormalizer->expects($this->never())->method('denormalize');

        $databasePlatform = new DatabasePlatformMock();
        $databasePlatform->expectFetch(
            'SELECT * FROM documents WHERE directory = :directory ORDER BY title',
            ['directory' => 'foo'],
            [],
        );

        $query     = new FindDocumentsByDirectoryQuery($denormalizer, $databasePlatform);
        $documents = $query->query(new FindDocumentsByDirectory('foo'));

        self::assertSame([], $documents);
    }

    #[Test]
    public function queryWithResults(): void
    {
        $searchDirectory = (new DirectoryBuilder())->build();

        $platform = new DatabasePlatformMock();
        $platform->expectFetch(
            'SELECT * FROM documents WHERE directory = :directory ORDER BY title',
            ['directory' => $searchDirectory->getId()],
            [
                ['title' => 'foo'],
                ['title' => 'bar'],
            ],
        );

        $denormalizer = $this->createMock(DenormalizerInterface::class);
        $denormalizer->expects($this->exactly(2))
            ->method('denormalize')
            ->willReturnCallback(
                static function (array $data, string $class) use ($searchDirectory): object {
                    self::assertSame(Document::class, $class);

                    if ($data === ['title' => 'foo']) {
                        return (new DocumentBuilder())
                            ->withTitle('foo')
                            ->withDirectory($searchDirectory)
                            ->withContent('foo')
                            ->build();
                    }

                    if ($data === ['title' => 'bar']) {
                        return (new DocumentBuilder())
                            ->withTitle('bar')
                            ->withDirectory($searchDirectory)
                            ->withContent('bar')
                            ->build();
                    }

                    throw new UnexpectedValueException('Unexpected content');
                },
            );

        $query     = new FindDocumentsByDirectoryQuery($denormalizer, $platform);
        $documents = $query->query(new FindDocumentsByDirectory($searchDirectory->getId()));

        self::assertCount(2, $documents);
        self::assertSame('foo', $documents[0]->getTitle());
    }
}
