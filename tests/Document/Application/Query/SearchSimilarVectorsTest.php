<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Document\Application\Query;

use ChronicleKeeper\Document\Application\Query\SearchSimilarVectors;
use ChronicleKeeper\Document\Application\Query\SearchSimilarVectorsQuery;
use ChronicleKeeper\Library\Infrastructure\VectorStorage\Distance\CosineDistance;
use ChronicleKeeper\Library\Infrastructure\VectorStorage\VectorDocument;
use ChronicleKeeper\Shared\Application\Query\QueryService;
use ChronicleKeeper\Test\Library\Domain\Entity\DocumentBuilder;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(SearchSimilarVectors::class)]
#[CoversClass(SearchSimilarVectorsQuery::class)]
#[Small]
class SearchSimilarVectorsTest extends TestCase
{
    #[Test]
    public function parametersAreInitializable(): void
    {
        $parameters = new SearchSimilarVectors([12.0], 0.5, 10);

        self::assertSame([12.0], $parameters->searchedVectors);
        self::assertSame(0.5, $parameters->maxDistance);
        self::assertSame(10, $parameters->maxResults);

        self::assertSame(SearchSimilarVectorsQuery::class, $parameters->getQueryClass());
    }

    #[Test]
    public function queryWorkingWithoutResults(): void
    {
        $queryService = $this->createMock(QueryService::class);
        $queryService->expects($this->once())->method('query')->willReturn([]);

        $cosineDistance = $this->createMock(CosineDistance::class);
        $cosineDistance->expects($this->never())->method('measure');

        $query = new SearchSimilarVectorsQuery($queryService, $cosineDistance);

        $documents = $query->query(new SearchSimilarVectors([12.0, 13.0], 0.5, 10));

        self::assertSame([], $documents);
    }

    #[Test]
    public function queryWithFilteredResults(): void
    {
        $firstDocument  = new VectorDocument((new DocumentBuilder())->build(), 'foo', 'foo', [12.0, 13.0]);
        $secondDocument = new VectorDocument((new DocumentBuilder())->build(), 'bar', 'bar', [14.0, 15.0]);

        $queryService = $this->createMock(QueryService::class);
        $queryService->expects($this->once())
            ->method('query')
            ->willReturn([$firstDocument, $secondDocument]);

        $cosineDistance = $this->createMock(CosineDistance::class);
        $cosineDistance->expects($this->exactly(2))
            ->method('measure')
            ->willReturnOnConsecutiveCalls(0.4, 0.6);

        $query = new SearchSimilarVectorsQuery($queryService, $cosineDistance);

        $documents = $query->query(new SearchSimilarVectors([12.0, 13.0], 0.5, 10));

        self::assertCount(1, $documents);
    }

    #[Test]
    public function queryRespectsMaximalResults(): void
    {
        $firstDocument  = new VectorDocument((new DocumentBuilder())->build(), 'foo', 'foo', [12.0, 13.0]);
        $secondDocument = new VectorDocument((new DocumentBuilder())->build(), 'bar', 'bar', [14.0, 15.0]);

        $queryService = $this->createMock(QueryService::class);
        $queryService->expects($this->once())
            ->method('query')
            ->willReturn([$firstDocument, $secondDocument]);

        $cosineDistance = $this->createMock(CosineDistance::class);
        $cosineDistance->expects($this->exactly(2))
            ->method('measure')->willReturn(0.4);

        $query = new SearchSimilarVectorsQuery($queryService, $cosineDistance);

        $documents = $query->query(new SearchSimilarVectors([12.0, 13.0], 0.5, 1));

        self::assertCount(1, $documents);
    }

    #[Test]
    public function documentWithEmptyContentIsIgnored(): void
    {
        $firstDocument  = new VectorDocument((new DocumentBuilder())->build(), 'foo', '', [12.0, 13.0]);
        $secondDocument = new VectorDocument(
            (new DocumentBuilder())->withContent('')->build(),
            'bar',
            'bar',
            [14.0, 15.0],
        );

        $queryService = $this->createMock(QueryService::class);
        $queryService->expects($this->once())
            ->method('query')
            ->willReturn([$firstDocument, $secondDocument]);

        $cosineDistance = $this->createMock(CosineDistance::class);
        $cosineDistance->expects($this->once())
            ->method('measure')
            ->willReturn(0.4);

        $query = new SearchSimilarVectorsQuery($queryService, $cosineDistance);

        $documents = $query->query(new SearchSimilarVectors([12.0, 13.0], 0.5, 10));

        self::assertCount(1, $documents);
    }

    #[Test]
    public function documentsAreOrderedByVectorDistance(): void
    {
        $firstDocument  = new VectorDocument((new DocumentBuilder())->build(), 'foo', 'foo', [12.0, 13.0]);
        $secondDocument = new VectorDocument((new DocumentBuilder())->build(), 'bar', 'bar', [14.0, 15.0]);

        $queryService = $this->createMock(QueryService::class);
        $queryService->expects($this->once())
            ->method('query')
            ->willReturn([$firstDocument, $secondDocument]);

        $cosineDistance = $this->createMock(CosineDistance::class);
        $cosineDistance->expects($this->exactly(2))
            ->method('measure')
            ->willReturnOnConsecutiveCalls(0.6, 0.4);

        $query = new SearchSimilarVectorsQuery($queryService, $cosineDistance);

        $documents = $query->query(new SearchSimilarVectors([12.0, 13.0], 1, 10));

        self::assertSame($secondDocument, $documents[0]['vector']);
        self::assertSame($firstDocument, $documents[1]['vector']);
    }
}
