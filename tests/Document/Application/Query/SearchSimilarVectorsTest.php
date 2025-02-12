<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Document\Application\Query;

use ChronicleKeeper\Document\Application\Command\StoreDocument;
use ChronicleKeeper\Document\Application\Command\StoreDocumentVectors;
use ChronicleKeeper\Document\Application\Query\SearchSimilarVectors;
use ChronicleKeeper\Document\Application\Query\SearchSimilarVectorsQuery;
use ChronicleKeeper\Test\Document\Domain\Entity\DocumentBuilder;
use ChronicleKeeper\Test\Document\Domain\Entity\VectorDocumentBuilder;
use ChronicleKeeper\Test\Shared\Infrastructure\Database\DatabaseTestCase;
use Override;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Large;
use PHPUnit\Framework\Attributes\Test;

use function array_fill;
use function assert;

#[CoversClass(SearchSimilarVectors::class)]
#[CoversClass(SearchSimilarVectorsQuery::class)]
#[Large]
class SearchSimilarVectorsTest extends DatabaseTestCase
{
    private SearchSimilarVectorsQuery $query;

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        $query = self::getContainer()->get(SearchSimilarVectorsQuery::class);
        assert($query instanceof SearchSimilarVectorsQuery);

        $this->query = $query;
    }

    #[Override]
    protected function tearDown(): void
    {
        parent::tearDown();

        unset($this->query);
    }

    #[Test]
    public function itHasConstructableQueryParameters(): void
    {
        $parameters = new SearchSimilarVectors([12.0], 0.5, 10);

        self::assertSame([12.0], $parameters->searchedVectors);
        self::assertSame(0.5, $parameters->maxDistance);
        self::assertSame(10, $parameters->maxResults);

        self::assertSame(SearchSimilarVectorsQuery::class, $parameters->getQueryClass());
    }

    #[Test]
    public function itWillResultInAnEmptyResultWhenThereAreNone(): void
    {
        $results = $this->query->query(new SearchSimilarVectors(
            array_fill(0, 1536, 0.0),
            0.5,
            10,
        ));

        self::assertEmpty($results);
    }

    #[Test]
    public function itWillReturnResultsWhenFound(): void
    {
        // ------------------- The test setup -------------------

        $document = (new DocumentBuilder())
            ->withId('4202ef81-3285-4203-9efb-8217f4e93554')
            ->build();

        $vectorDocument = (new VectorDocumentBuilder())
            ->withDocument($document)
            ->hasFooVector()
            ->build();

        $this->bus->dispatch(new StoreDocument($document));
        $this->bus->dispatch(new StoreDocumentVectors($vectorDocument));

        // ------------------- The test execution -------------------

        $results = $this->query->query(new SearchSimilarVectors(
            $vectorDocument->vector, // Utilize the vector of the document to search the document
            0.5,
            10,
        ));

        // ------------------- The test assertions -------------------

        self::assertCount(1, $results);
        self::assertSame('4202ef81-3285-4203-9efb-8217f4e93554', $results[0]['document']->getId());
        self::assertSame(0.0, $results[0]['distance']); // It is totally equal!
    }
}
