<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Image\Application\Query;

use ChronicleKeeper\Image\Application\Command\StoreImage;
use ChronicleKeeper\Image\Application\Query\FindAllImages;
use ChronicleKeeper\Image\Application\Query\FindAllImagesQuery;
use ChronicleKeeper\Test\Image\Domain\Entity\ImageBuilder;
use ChronicleKeeper\Test\Shared\Infrastructure\Database\SQLite\DatabaseTestCase;
use Override;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Large;
use PHPUnit\Framework\Attributes\Test;

use function assert;

#[CoversClass(FindAllImagesQuery::class)]
#[CoversClass(FindAllImages::class)]
#[Large]
final class FindAllImagesTest extends DatabaseTestCase
{
    private FindAllImagesQuery $query;

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        $query = self::getContainer()->get(FindAllImagesQuery::class);
        assert($query instanceof FindAllImagesQuery);

        $this->query = $query;
    }

    #[Override]
    protected function tearDown(): void
    {
        parent::tearDown();

        unset($this->query);
    }

    #[Test]
    public function itHasACorrectQueryClassInUse(): void
    {
        self::assertSame(FindAllImagesQuery::class, (new FindAllImages())->getQueryClass());
    }

    #[Test]
    public function itIsAbleToFetchAllImages(): void
    {
        // ------------------- The test setup -------------------

        $image = (new ImageBuilder())
            ->withId('b48d23bc-7a04-4483-81da-56c72dbdc628')
            ->withTitle('foo')
            ->build();
        $this->bus->dispatch(new StoreImage($image));

        $image = (new ImageBuilder())
            ->withId('71fabba4-7a4e-4758-b07e-0ebaa2063748')
            ->withTitle('bar')
            ->build();
        $this->bus->dispatch(new StoreImage($image));

        // ------------------- The test scenario -------------------

        $images = $this->query->query(new FindAllImages());

        // ------------------- The test assertions -------------------

        self::assertCount(2, $images);
        self::assertSame('71fabba4-7a4e-4758-b07e-0ebaa2063748', $images[0]->getId());
        self::assertSame('b48d23bc-7a04-4483-81da-56c72dbdc628', $images[1]->getId());
    }
}
