<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Image\Application\Query;

use ChronicleKeeper\Image\Application\Command\StoreImage;
use ChronicleKeeper\Image\Application\Command\StoreImageVectors;
use ChronicleKeeper\Image\Application\Query\SearchSimilarImages;
use ChronicleKeeper\Image\Application\Query\SearchSimilarImagesQuery;
use ChronicleKeeper\Test\Image\Domain\Entity\ImageBuilder;
use ChronicleKeeper\Test\Image\Domain\Entity\VectorImageBuilder;
use ChronicleKeeper\Test\Shared\Infrastructure\Database\SQLite\DatabaseTestCase;
use Override;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Large;
use PHPUnit\Framework\Attributes\Test;

use function array_fill;
use function assert;

#[CoversClass(SearchSimilarImages::class)]
#[CoversClass(SearchSimilarImagesQuery::class)]
#[Large]
final class SearchSimilarImagesTest extends DatabaseTestCase
{
    private SearchSimilarImagesQuery $query;

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        $query = self::getContainer()->get(SearchSimilarImagesQuery::class);
        assert($query instanceof SearchSimilarImagesQuery);

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
        $parameters = new SearchSimilarImages([12.0], 0.5, 10);

        self::assertSame([12.0], $parameters->searchedVectors);
        self::assertSame(0.5, $parameters->maxDistance);
        self::assertSame(10, $parameters->maxResults);

        self::assertSame(SearchSimilarImagesQuery::class, $parameters->getQueryClass());
    }

    #[Test]
    public function itWillResultInAnEmptyResultWhenThereAreNone(): void
    {
        $results = $this->query->query(new SearchSimilarImages(
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

        $image = (new ImageBuilder())
            ->withId('4202ef81-3285-4203-9efb-8217f4e93554')
            ->build();

        $vectorImage = (new VectorImageBuilder())
            ->withImage($image)
            ->hasFooVector()
            ->build();

        $this->bus->dispatch(new StoreImage($image));
        $this->bus->dispatch(new StoreImageVectors($vectorImage));

        // ------------------- The test execution -------------------

        $results = $this->query->query(new SearchSimilarImages(
            $vectorImage->vector, // Utilize the vector of the document to search the document
            0.5,
            10,
        ));

        // ------------------- The test assertions -------------------

        self::assertCount(1, $results);
        self::assertSame('4202ef81-3285-4203-9efb-8217f4e93554', $results[0]['image']->getId());
        self::assertSame(0.0, $results[0]['distance']); // It is totally equal!
    }
}
