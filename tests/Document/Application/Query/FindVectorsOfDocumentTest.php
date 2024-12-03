<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Document\Application\Query;

use ChronicleKeeper\Document\Application\Query\FindVectorsOfDocument;
use ChronicleKeeper\Document\Application\Query\FindVectorsOfDocumentQuery;
use ChronicleKeeper\Library\Infrastructure\VectorStorage\VectorDocument;
use ChronicleKeeper\Shared\Application\Query\QueryService;
use ChronicleKeeper\Test\Library\Domain\Entity\DocumentBuilder;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

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
        $queryService = $this->createMock(QueryService::class);
        $queryService->expects($this->once())->method('query')->willReturn([]);

        $query = new FindVectorsOfDocumentQuery($queryService);

        $documents = $query->query(new FindVectorsOfDocument('foo'));

        self::assertSame([], $documents);
    }

    #[Test]
    public function queryWithFilteredResults(): void
    {
        $firstDocument  = new VectorDocument((new DocumentBuilder())->build(), 'foo', 'foo', []);
        $secondDocument = new VectorDocument((new DocumentBuilder())->build(), 'bar', 'bar', []);

        $queryService = $this->createMock(QueryService::class);
        $queryService->expects($this->once())
            ->method('query')
            ->willReturn([$firstDocument, $secondDocument]);

        $query = new FindVectorsOfDocumentQuery($queryService);

        $documents = $query->query(new FindVectorsOfDocument($firstDocument->document->id));

        self::assertCount(1, $documents);
    }
}
