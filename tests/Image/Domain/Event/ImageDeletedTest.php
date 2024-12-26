<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Image\Domain\Event;

use ChronicleKeeper\Image\Domain\Event\ImageDeleted;
use ChronicleKeeper\Test\Image\Domain\Entity\ImageBuilder;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(ImageDeleted::class)]
#[Small]
final class ImageDeletedTest extends TestCase
{
    #[Test]
    public function itCanBeConstructed(): void
    {
        $image = (new ImageBuilder())->build();
        $event = new ImageDeleted($image);

        self::assertSame($image, $event->image);
    }
}
