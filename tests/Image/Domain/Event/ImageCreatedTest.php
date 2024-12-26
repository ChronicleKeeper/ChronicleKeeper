<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Image\Domain\Event;

use ChronicleKeeper\Image\Domain\Event\ImageCreated;
use ChronicleKeeper\Test\Image\Domain\Entity\ImageBuilder;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(ImageCreated::class)]
#[Small]
final class ImageCreatedTest extends TestCase
{
    #[Test]
    public function itCanBeConstructed(): void
    {
        $image = (new ImageBuilder())->build();
        $event = new ImageCreated($image);

        self::assertSame($image, $event->image);
    }
}
