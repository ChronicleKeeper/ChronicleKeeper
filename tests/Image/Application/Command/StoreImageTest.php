<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Image\Application\Command;

use ChronicleKeeper\Image\Application\Command\StoreImage;
use ChronicleKeeper\Image\Application\Command\StoreImageHandler;
use ChronicleKeeper\Test\Image\Domain\Entity\ImageBuilder;
use ChronicleKeeper\Test\Shared\Infrastructure\Database\DatabasePlatformMock;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(StoreImage::class)]
#[CoversClass(StoreImageHandler::class)]
#[Small]
class StoreImageTest extends TestCase
{
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
        $databasePlatform = new DatabasePlatformMock();

        $handler = new StoreImageHandler($databasePlatform);
        $handler(new StoreImage($image = (new ImageBuilder())->build()));

        $databasePlatform->assertExecutedInsert(
            'images',
            [
                'id' => $image->getId(),
                'title' => $image->getTitle(),
                'mime_type' => $image->getMimeType(),
                'encoded_image' => $image->getEncodedImage(),
                'description' => $image->getDescription(),
                'directory' => $image->getDirectory()->getId(),
                'last_updated' => $image->getUpdatedAt()->format('Y-m-d H:i:s'),
            ],
        );
    }
}
