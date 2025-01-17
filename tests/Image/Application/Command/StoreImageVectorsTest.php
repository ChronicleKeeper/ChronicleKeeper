<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Image\Application\Command;

use ChronicleKeeper\Image\Application\Command\StoreImageVectors;
use ChronicleKeeper\Image\Application\Command\StoreImageVectorsHandler;
use ChronicleKeeper\Test\Image\Domain\Entity\VectorImageBuilder;
use ChronicleKeeper\Test\Shared\Infrastructure\Database\DatabasePlatformMock;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

use function implode;

#[CoversClass(StoreImageVectors::class)]
#[CoversClass(StoreImageVectorsHandler::class)]
#[Small]
class StoreImageVectorsTest extends TestCase
{
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
        $databasePlatform = new DatabasePlatformMock();

        $handler = new StoreImageVectorsHandler($databasePlatform);
        $handler(new StoreImageVectors($embedding = (new VectorImageBuilder())->build()));

        $databasePlatform->assertExecutedInsert(
            'images_vectors',
            [
                'image_id' => $embedding->image->getId(),
                'embedding' => '[' . implode(',', $embedding->vector) . ']',
                'content' => $embedding->content,
                'vectorContentHash' => $embedding->vectorContentHash,
            ],
        );
    }
}
