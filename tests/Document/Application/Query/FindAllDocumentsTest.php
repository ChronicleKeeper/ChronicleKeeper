<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Document\Application\Query;

use ChronicleKeeper\Document\Application\Query\FindAllDocuments;
use ChronicleKeeper\Document\Application\Query\FindAllDocumentsQuery;
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
        $denormalizer = $this->createMock(DenormalizerInterface::class);
        $denormalizer->expects($this->never())->method('denormalize');

        $databasePlatform = new DatabasePlatformMock();
        $databasePlatform->expectFetch('SELECT * FROM documents ORDER BY title', [], []);

        $query     = new FindAllDocumentsQuery($denormalizer, $databasePlatform);
        $documents = $query->query(new FindAllDocuments());

        $databasePlatform->assertFetchCount(1);
        self::assertSame([], $documents);
    }

    #[Test]
    public function queryWithSortedResults(): void
    {
        $databasePlatform = new DatabasePlatformMock();
        $databasePlatform->expectFetch(
            'SELECT * FROM documents ORDER BY title',
            [],
            [
                ['title' => 'bar'],
                ['title' => 'foo'],
            ],
        );

        $denormalizer = $this->createMock(DenormalizerInterface::class);
        $denormalizer->expects($this->exactly(2))
            ->method('denormalize')
            ->willReturnCallback(
                static function (array $data, string $class): object {
                    self::assertSame(Document::class, $class);

                    $directory = (new DirectoryBuilder())->build();

                    if ($data === ['title' => 'foo']) {
                        return (new DocumentBuilder())
                            ->withTitle('foo')
                            ->withDirectory($directory)
                            ->withContent('foo')
                            ->build();
                    }

                    if ($data === ['title' => 'bar']) {
                        return (new DocumentBuilder())
                            ->withTitle('bar')
                            ->withDirectory($directory)
                            ->withContent('bar')
                            ->build();
                    }

                    throw new UnexpectedValueException('Unexpected content');
                },
            );

        $query = new FindAllDocumentsQuery($denormalizer, $databasePlatform);

        $documents = $query->query(new FindAllDocuments());

        self::assertCount(2, $documents);
        self::assertSame('bar', $documents[0]->getTitle());
        self::assertSame('foo', $documents[1]->getTitle());
    }
}
