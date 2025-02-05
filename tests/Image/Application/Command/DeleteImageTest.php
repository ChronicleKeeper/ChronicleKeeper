<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Image\Application\Command;

use ChronicleKeeper\Image\Application\Command\DeleteImage;
use ChronicleKeeper\Image\Application\Command\DeleteImageHandler;
use ChronicleKeeper\Image\Application\Command\StoreImage;
use ChronicleKeeper\Image\Application\Command\StoreImageVectors;
use ChronicleKeeper\Image\Domain\Event\ImageDeleted;
use ChronicleKeeper\Test\Image\Domain\Entity\ImageBuilder;
use ChronicleKeeper\Test\Image\Domain\Entity\VectorImageBuilder;
use ChronicleKeeper\Test\Shared\Infrastructure\Database\SQLite\DatabaseTestCase;
use Override;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Large;
use PHPUnit\Framework\Attributes\Test;

use function assert;

#[CoversClass(DeleteImage::class)]
#[CoversClass(DeleteImageHandler::class)]
#[Large]
class DeleteImageTest extends DatabaseTestCase
{
    private DeleteImageHandler $handler;

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        $handler = self::getContainer()->get(DeleteImageHandler::class);
        assert($handler instanceof DeleteImageHandler);

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
        $image   = (new ImageBuilder())->build();
        $command = new DeleteImage($image);

        self::assertSame($image, $command->image);
    }

    #[Test]
    public function itWilLDeleteAllImagesWithTheirEmbeddings(): void
    {
        // ------------------- The test setup -------------------

        $image = (new ImageBuilder())->build();
        $this->bus->dispatch(new StoreImage($image));

        $imageVectors = (new VectorImageBuilder())->withImage($image)->build();
        $this->bus->dispatch(new StoreImageVectors($imageVectors));

        // ------------------- The test execution -------------------

        $result = ($this->handler)(new DeleteImage($image));

        // ------------------- The test assertions -------------------

        self::assertCount(1, $result->getEvents());
        self::assertInstanceOf(ImageDeleted::class, $result->getEvents()[0]);

        $this->assertTableIsEmpty('images');
        $this->assertTableIsEmpty('images_vectors');
    }
}
