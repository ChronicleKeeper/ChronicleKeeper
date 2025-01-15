<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Image\Application\Command;

use ChronicleKeeper\Image\Application\Command\DeleteImage;
use ChronicleKeeper\Image\Application\Command\DeleteImageHandler;
use ChronicleKeeper\Image\Application\Command\DeleteImageVectors;
use ChronicleKeeper\Image\Domain\Event\ImageDeleted;
use ChronicleKeeper\Test\Image\Domain\Entity\ImageBuilder;
use ChronicleKeeper\Test\Shared\Infrastructure\Database\DatabasePlatformMock;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;

#[CoversClass(DeleteImage::class)]
#[CoversClass(DeleteImageHandler::class)]
#[Small]
class DeleteImageTest extends TestCase
{
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
        $image = (new ImageBuilder())->withId('065abf19-0d7d-44a0-9b24-ad2f1ee88320')->build();

        $bus = $this->createMock(MessageBusInterface::class);
        $bus->expects($this->once())
            ->method('dispatch')
            ->willReturnCallback(static function (object $message) use ($image): Envelope {
                self::assertInstanceOf(DeleteImageVectors::class, $message);
                self::assertSame($image->getId(), $message->imageId);

                return new Envelope($message);
            });

        $databasePlatform = new DatabasePlatformMock();
        $handler          = new DeleteImageHandler($databasePlatform, $bus);
        $result           = $handler(new DeleteImage($image));

        $databasePlatform->assertExecutedQuery(
            'DELETE FROM images WHERE id = :id',
            ['id' => '065abf19-0d7d-44a0-9b24-ad2f1ee88320'],
        );

        self::assertCount(1, $result->getEvents());
        self::assertInstanceOf(ImageDeleted::class, $result->getEvents()[0]);
    }
}
