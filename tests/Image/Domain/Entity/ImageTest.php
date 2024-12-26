<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Image\Domain\Entity;

use ChronicleKeeper\Image\Domain\Entity\Image;
use ChronicleKeeper\Image\Domain\Event\ImageCreated;
use ChronicleKeeper\Image\Domain\Event\ImageDescriptionUpdated;
use ChronicleKeeper\Image\Domain\Event\ImageMovedToDirectory;
use ChronicleKeeper\Image\Domain\Event\ImageRenamed;
use ChronicleKeeper\Library\Domain\RootDirectory;
use ChronicleKeeper\Test\Library\Domain\Entity\DirectoryBuilder;
use DateTimeImmutable;
use DateTimeInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

use function base64_encode;
use function json_encode;

use const JSON_THROW_ON_ERROR;

#[CoversClass(Image::class)]
#[Small]
final class ImageTest extends TestCase
{
    #[Test]
    public function itCanBeConstructed(): void
    {
        $image         = new Image(
            'id',
            'title',
            'mime_type',
            'encoded_image',
            'description',
            $directory = (new DirectoryBuilder())->build(),
            $updatedAt = new DateTimeImmutable(),
        );

        self::assertSame('id', $image->getId());
        self::assertSame('title', $image->getTitle());
        self::assertSame('mime_type', $image->getMimeType());
        self::assertSame('encoded_image', $image->getEncodedImage());
        self::assertSame('description', $image->getDescription());
        self::assertSame($directory, $image->getDirectory());
        self::assertSame($updatedAt, $image->getUpdatedAt());

        // Test there are no events recorded on construction
        $events = $image->flushEvents();
        self::assertCount(0, $events);
    }

    #[Test]
    public function itCanBeCreatedWithoutOptionalArguments(): void
    {
        $image = Image::create(
            'title',
            'mime_type',
            'encoded_image',
            'description',
        );

        self::assertSame('title', $image->getTitle());
        self::assertSame('mime_type', $image->getMimeType());
        self::assertSame('encoded_image', $image->getEncodedImage());
        self::assertSame('description', $image->getDescription());
        self::assertSame(RootDirectory::ID, $image->getDirectory()->id);

        // Test events are recorded
        $events = $image->flushEvents();
        self::assertCount(1, $events);
        self::assertInstanceOf(ImageCreated::class, $events[0]);
    }

    #[Test]
    public function itCanBeCreatedWithSpecificDirectory(): void
    {
        $directory = (new DirectoryBuilder())->build();
        $image     = Image::create(
            'title',
            'mime_type',
            'encoded_image',
            'description',
            $directory,
        );

        self::assertSame($directory, $image->getDirectory());

        // Test events are recorded
        $events = $image->flushEvents();
        self::assertCount(1, $events);
        self::assertInstanceOf(ImageCreated::class, $events[0]);
    }

    #[Test]
    public function itCanBeRenamed(): void
    {
        $image = (new ImageBuilder())
            ->withUpdatedAt($updatedAt = new DateTimeImmutable())
            ->build();
        $image->rename('new title');

        self::assertSame('new title', $image->getTitle());
        self::assertNotSame($updatedAt, $image->getUpdatedAt());

        // Test events are recorded
        $events = $image->flushEvents();
        self::assertCount(1, $events);
        self::assertInstanceOf(ImageRenamed::class, $events[0]);
    }

    #[Test]
    public function itCanNotBeRenamedWithSameTitle(): void
    {
        $image = (new ImageBuilder())
            ->withUpdatedAt($updatedAt = new DateTimeImmutable())
            ->build();
        $image->rename($image->getTitle());

        self::assertSame($updatedAt, $image->getUpdatedAt());

        // Test no events are recorded
        $events = $image->flushEvents();
        self::assertCount(0, $events);
    }

    #[Test]
    public function itCanGetAChangedDescription(): void
    {
        $image = (new ImageBuilder())
            ->withUpdatedAt($updatedAt = new DateTimeImmutable())
            ->build();

        $image->updateDescription('new description');

        self::assertSame('new description', $image->getDescription());
        self::assertNotSame($updatedAt, $image->getUpdatedAt());

        // Test events are recorded
        $events = $image->flushEvents();
        self::assertCount(1, $events);
        self::assertInstanceOf(ImageDescriptionUpdated::class, $events[0]);
    }

    #[Test]
    public function itCanNotUpdateDescriptionWithSameValue(): void
    {
        $image = (new ImageBuilder())
            ->withUpdatedAt($updatedAt = new DateTimeImmutable())
            ->build();

        $image->updateDescription($image->getDescription());

        self::assertSame($updatedAt, $image->getUpdatedAt());

        // Test no events are recorded
        $events = $image->flushEvents();
        self::assertCount(0, $events);
    }

    #[Test]
    public function itCanBeMovedToAnotherDirectory(): void
    {
        $image = (new ImageBuilder())
            ->withUpdatedAt($updatedAt = new DateTimeImmutable())
            ->build();

        $image->moveToDirectory($newDirectory = (new DirectoryBuilder())->build());

        self::assertSame($newDirectory, $image->getDirectory());
        self::assertNotSame($updatedAt, $image->getUpdatedAt());

        // Test events are recorded
        $events = $image->flushEvents();
        self::assertCount(1, $events);
        self::assertInstanceOf(ImageMovedToDirectory::class, $events[0]);
    }

    #[Test]
    public function itCanNotBeMovedToSameDirectory(): void
    {
        $image = (new ImageBuilder())
            ->withUpdatedAt($updatedAt = new DateTimeImmutable())
            ->build();

        $image->moveToDirectory($image->getDirectory());

        self::assertSame($updatedAt, $image->getUpdatedAt());

        // Test no events are recorded
        $events = $image->flushEvents();
        self::assertCount(0, $events);
    }

    #[Test]
    public function itCanBeConvertedToAnArray(): void
    {
        $image = (new ImageBuilder())->build();
        $array = $image->toArray();

        self::assertSame($image->getId(), $array['id']);
        self::assertSame($image->getTitle(), $array['title']);
        self::assertSame($image->getMimeType(), $array['mime_type']);
        self::assertSame($image->getEncodedImage(), $array['encoded_image']);
        self::assertSame($image->getDescription(), $array['description']);
        self::assertSame($image->getDirectory()->id, $array['directory']);
        self::assertSame(
            $image->getUpdatedAt()->format(DateTimeInterface::ATOM),
            $array['last_updated'],
        );
    }

    #[Test]
    public function testItCanBeJsonSerialized(): void
    {
        $image = (new ImageBuilder())->build();
        $json  = json_encode($image, JSON_THROW_ON_ERROR);

        self::assertJson($json);
    }

    #[Test]
    public function itCanBeSlugged(): void
    {
        $image = (new ImageBuilder())
            ->withTitle('Default title with ...')
            ->build();
        $slug  = $image->getSlug();

        self::assertSame('Default-title-with', $slug);
    }

    #[Test]
    public function itCanBeConvertedToADataImageUrl(): void
    {
        $image   = (new ImageBuilder())->build();
        $dataUrl = $image->getImageUrl();

        self::assertStringStartsWith('data:', $dataUrl);
        self::assertStringContainsString($image->getMimeType(), $dataUrl);
        self::assertStringContainsString($image->getEncodedImage(), $dataUrl);
    }

    #[Test]
    public function itCanGiveACorrectDescriptionHash(): void
    {
        $image = (new ImageBuilder())
            ->withDescription('This is a description')
            ->build();

        self::assertSame('72f7e0ea97e14eff4e67c21f69c65319129bcfc5', $image->getDescriptionHash());
    }

    #[Test]
    public function itCanCalculateTheImageSizeWithAnInvalidBase64String(): void
    {
        $image = (new ImageBuilder())
            ->withEncodedImage('invalid-base64')
            ->build();

        self::assertSame(0, $image->getSize());
    }

    #[Test]
    public function itCanCalculateTheImageSize(): void
    {
        $image = (new ImageBuilder())
            ->withEncodedImage(base64_encode('image-data'))
            ->build();

        self::assertSame(10, $image->getSize());
    }
}
