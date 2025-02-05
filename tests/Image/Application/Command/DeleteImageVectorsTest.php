<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Image\Application\Command;

use ChronicleKeeper\Image\Application\Command\DeleteImageVectors;
use ChronicleKeeper\Image\Application\Command\DeleteImageVectorsHandler;
use ChronicleKeeper\Image\Application\Command\StoreImageVectors;
use ChronicleKeeper\Test\Image\Domain\Entity\VectorImageBuilder;
use ChronicleKeeper\Test\Shared\Infrastructure\Database\SQLite\DatabaseTestCase;
use Override;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Large;
use PHPUnit\Framework\Attributes\Test;

use function assert;

#[CoversClass(DeleteImageVectors::class)]
#[CoversClass(DeleteImageVectorsHandler::class)]
#[Large]
class DeleteImageVectorsTest extends DatabaseTestCase
{
    private DeleteImageVectorsHandler $handler;

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        $handler = self::getContainer()->get(DeleteImageVectorsHandler::class);
        assert($handler instanceof DeleteImageVectorsHandler);

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
        $imageId = '065abf19-0d7d-44a0-9b24-ad2f1ee88320';
        $command = new DeleteImageVectors($imageId);

        self::assertSame($imageId, $command->imageId);
    }

    #[Test]
    public function itWillDeleteAllVectors(): void
    {
        // ------------------- The test setup -------------------

        $imageVectors = (new VectorImageBuilder())->build();
        $this->bus->dispatch(new StoreImageVectors($imageVectors));

        // ------------------- The test setup -------------------

        ($this->handler)(new DeleteImageVectors($imageVectors->image->getId()));

        // ------------------- The test execution -------------------

        $this->assertTableIsEmpty('images_vectors');
    }
}
