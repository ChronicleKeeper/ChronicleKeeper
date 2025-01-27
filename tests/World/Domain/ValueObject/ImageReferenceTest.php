<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\World\Domain\ValueObject;

use ChronicleKeeper\Test\Image\Domain\Entity\ImageBuilder;
use ChronicleKeeper\Test\World\Domain\Entity\ItemBuilder;
use ChronicleKeeper\World\Domain\ValueObject\ImageReference;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(ImageReference::class)]
#[Small]
final class ImageReferenceTest extends TestCase
{
    #[Test]
    public function itIsConstructable(): void
    {
        $item      = (new ItemBuilder())->build();
        $image     = (new ImageBuilder())->build();
        $reference = new ImageReference($item, $image);

        self::assertSame($item, $reference->item);
        self::assertSame($image, $reference->image);
    }

    #[Test]
    public function itReturnsCorrectType(): void
    {
        $item      = (new ItemBuilder())->build();
        $image     = (new ImageBuilder())->build();
        $reference = new ImageReference($item, $image);

        self::assertSame('image', $reference->getType());
    }

    #[Test]
    public function itReturnsCorrectIcon(): void
    {
        $item      = (new ItemBuilder())->build();
        $image     = (new ImageBuilder())->build();
        $reference = new ImageReference($item, $image);

        self::assertSame('tabler:photo', $reference->getIcon());
    }

    #[Test]
    public function itReturnsCorrectMediaId(): void
    {
        $item      = (new ItemBuilder())->build();
        $image     = (new ImageBuilder())->build();
        $reference = new ImageReference($item, $image);

        self::assertSame($image->getId(), $reference->getMediaId());
    }

    #[Test]
    public function itReturnsCorrectMediaTitle(): void
    {
        $item      = (new ItemBuilder())->build();
        $image     = (new ImageBuilder())->withTitle('Image Title')->build();
        $reference = new ImageReference($item, $image);

        self::assertSame($image->getTitle(), $reference->getMediaTitle());
    }

    #[Test]
    public function itReturnsCorrectMediaDisplayName(): void
    {
        $item      = (new ItemBuilder())->build();
        $image     = (new ImageBuilder())->build();
        $reference = new ImageReference($item, $image);

        self::assertSame('Hauptverzeichnis > ' . $image->getTitle(), $reference->getMediaDisplayName());
    }

    #[Test]
    public function itReturnsCorrectGenericLinkIdentifier(): void
    {
        $item      = (new ItemBuilder())->build();
        $image     = (new ImageBuilder())->build();
        $reference = new ImageReference($item, $image);

        self::assertSame('image_' . $image->getId(), $reference->getGenericLinkIdentifier());
    }
}
