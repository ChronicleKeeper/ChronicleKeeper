<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Image\Application\Query;

use ChronicleKeeper\Image\Application\Command\StoreImage;
use ChronicleKeeper\Image\Application\Query\GetImage;
use ChronicleKeeper\Image\Application\Query\GetImageQuery;
use ChronicleKeeper\Test\Image\Domain\Entity\ImageBuilder;
use ChronicleKeeper\Test\Shared\Infrastructure\Database\SQLite\DatabaseTestCase;
use Override;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Large;
use PHPUnit\Framework\Attributes\Test;

use function assert;

#[CoversClass(GetImage::class)]
#[CoversClass(GetImageQuery::class)]
#[Large]
class GetImageTest extends DatabaseTestCase
{
    private GetImageQuery $query;

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        $query = self::getContainer()->get(GetImageQuery::class);
        assert($query instanceof GetImageQuery);

        $this->query = $query;
    }

    #[Override]
    protected function tearDown(): void
    {
        parent::tearDown();

        unset($this->query);
    }

    #[Test]
    public function itIsUtilizingTheCorrectQueryClass(): void
    {
        $query = new GetImage('b22e89e8-54b0-4abd-b8d2-8a4c7f5c3150');

        self::assertSame(GetImageQuery::class, $query->getQueryClass());
        self::assertSame('b22e89e8-54b0-4abd-b8d2-8a4c7f5c3150', $query->id);
    }

    #[Test]
    public function itIsSelectingTheCorrectImageFromDatabase(): void
    {
        // ------------------- The test scenario -------------------

        $image = (new ImageBuilder())
            ->withId('b48d23bc-7a04-4483-81da-56c72dbdc628')
            ->build();
        $this->bus->dispatch(new StoreImage($image));

        // ------------------- The test assertions -------------------

        $image = $this->query->query(new GetImage($image->getId()));

        // ------------------- The test assertions -------------------

        self::assertSame('b48d23bc-7a04-4483-81da-56c72dbdc628', $image->getId());
    }
}
