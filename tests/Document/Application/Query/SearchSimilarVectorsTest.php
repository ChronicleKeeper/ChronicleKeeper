<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Document\Application\Query;

use ChronicleKeeper\Document\Application\Query\GetDocument;
use ChronicleKeeper\Document\Application\Query\SearchSimilarVectors;
use ChronicleKeeper\Document\Application\Query\SearchSimilarVectorsQuery;
use ChronicleKeeper\Shared\Application\Query\QueryService;
use ChronicleKeeper\Test\Document\Domain\Entity\DocumentBuilder;
use ChronicleKeeper\Test\Shared\Infrastructure\Database\DatabasePlatformMock;
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
    public function itWillResultInAnEmptyArrayWhenNotResultsFound(): void
    {
        $query = new SearchSimilarVectors([12.0], 0.5, 10);

        $queryService = $this->createMock(QueryService::class);
        $queryService->expects($this->never())->method('query')->willReturn([]);

        $databasePlatform = new DatabasePlatformMock();
        $databasePlatform->expectAnyFetchWithJustParameters(
            [
                'embedding' => '[12]',
                'maxDistance' => 0.5,
                'maxResults' => 10,
            ],
            [],
        );

        $searchSimilarVectorsQuery = new SearchSimilarVectorsQuery($databasePlatform, $queryService);
        $results                   = $searchSimilarVectorsQuery->query($query);

        self::assertEmpty($results);
    }

    #[Test]
    public function itWillReturnResultsWhenFound(): void
    {
        $responseDocument = (new DocumentBuilder())->build();
        $queryService     = $this->createMock(QueryService::class);
        $queryService->expects($this->once())
            ->method('query')
            ->willReturnCallback(
                static function (GetDocument $query) use ($responseDocument) {
                    self::assertSame('1', $query->id);

                    return $responseDocument;
                },
            );

        $databasePlatform = new DatabasePlatformMock();
        $databasePlatform->expectAnyFetchWithJustParameters(
            [
                'embedding' => '[12]',
                'maxDistance' => 0.5,
                'maxResults' => 10,
            ],
            [['document_id' => '1', 'distance' => 0.1, 'content' => 'content']],
        );

        $searchSimilarVectorsQuery = new SearchSimilarVectorsQuery($databasePlatform, $queryService);
        $results                   = $searchSimilarVectorsQuery->query(new SearchSimilarVectors([12.0], 0.5, 10));

        self::assertSame([['document' => $responseDocument, 'content' => 'content', 'distance' => 0.1]], $results);
    }
}
