<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Image\Application\Command;

use ChronicleKeeper\Image\Application\Command\StoreImageVectors;
use ChronicleKeeper\Image\Application\Command\StoreImageVectorsHandler;
use ChronicleKeeper\Test\Image\Domain\Entity\VectorImageBuilder;
use ChronicleKeeper\Test\Shared\Infrastructure\Database\DatabaseTestCase;
use Override;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Large;
use PHPUnit\Framework\Attributes\Test;

use function assert;

#[CoversClass(StoreImageVectors::class)]
#[CoversClass(StoreImageVectorsHandler::class)]
#[Large]
class StoreImageVectorsTest extends DatabaseTestCase
{
    private StoreImageVectorsHandler $handler;

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        $handler = self::getContainer()->get(StoreImageVectorsHandler::class);
        assert($handler instanceof StoreImageVectorsHandler);

        $this->handler = $handler;
    }

    #[Override]
    protected function tearDown(): void
    {
        parent::tearDown();

        unset($this->handler);
    }

    #[Test]
    public function itHasACreatableCommand(): void
    {
        $vectorImage = (new VectorImageBuilder())->build();
        $command     = new StoreImageVectors($vectorImage);

        self::assertSame($vectorImage, $command->vectorImage);
    }

    #[Test]
    public function itWillStoreEmbeddings(): void
    {
        // ------------------- The test scenario -------------------

        ($this->handler)(new StoreImageVectors($vectorImage = (new VectorImageBuilder())->build()));

        // ------------------- The test assertions -------------------

        $this->assertRowsInTable('images_vectors', 1);
    }
}
