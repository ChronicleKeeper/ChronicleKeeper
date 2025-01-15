<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Image\Application\Command;

use ChronicleKeeper\Image\Application\Command\DeleteImageVectors;
use ChronicleKeeper\Image\Application\Command\DeleteImageVectorsHandler;
use ChronicleKeeper\Test\Shared\Infrastructure\Database\DatabasePlatformMock;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(DeleteImageVectors::class)]
#[CoversClass(DeleteImageVectorsHandler::class)]
#[Small]
class DeleteImageVectorsTest extends TestCase
{
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
        $imageId = '065abf19-0d7d-44a0-9b24-ad2f1ee88320';

        $databasePlatform = new DatabasePlatformMock();
        $handler          = new DeleteImageVectorsHandler($databasePlatform);
        $handler(new DeleteImageVectors($imageId));

        $databasePlatform->assertExecutedQuery(
            'DELETE FROM images_vectors WHERE image_id = :id',
            ['id' => $imageId],
        );
    }
}
