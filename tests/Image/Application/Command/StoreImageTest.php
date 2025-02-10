<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Image\Application\Command;

use ChronicleKeeper\Image\Application\Command\StoreImage;
use ChronicleKeeper\Image\Application\Command\StoreImageHandler;
use ChronicleKeeper\Test\Image\Domain\Entity\ImageBuilder;
use ChronicleKeeper\Test\Shared\Infrastructure\Database\DatabaseTestCase;
use Override;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Large;
use PHPUnit\Framework\Attributes\Test;

use function assert;

#[CoversClass(StoreImage::class)]
#[CoversClass(StoreImageHandler::class)]
#[Large]
class StoreImageTest extends DatabaseTestCase
{
    private StoreImageHandler $handler;

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        $handler = self::getContainer()->get(StoreImageHandler::class);
        assert($handler instanceof StoreImageHandler);

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
        $command = new StoreImage($image);

        self::assertSame($image, $command->image);
    }

    #[Test]
    public function itWillStoreAnImage(): void
    {
        // ------------------- The test scenario -------------------

        ($this->handler)(new StoreImage($image = (new ImageBuilder())->build()));

        // ------------------- The test assertions -------------------

        $this->assertRowsInTable('images', 1);

        $rawImage = $this->getRowFromTable('images', 'id', $image->getId());
        self::assertNotNull($rawImage);
        self::assertSame($rawImage['id'], $image->getId());
        self::assertSame($rawImage['title'], $image->getTitle());
    }
}
