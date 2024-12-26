<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Image\Domain\Event;

use ChronicleKeeper\Image\Domain\Event\ImageDescriptionUpdated;
use ChronicleKeeper\Test\Image\Domain\Entity\ImageBuilder;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(ImageDescriptionUpdated::class)]
#[Small]
final class ImageDescriptionUpdatedTest extends TestCase
{
    #[Test]
    public function itCanBeConstructed(): void
    {
        $image = (new ImageBuilder())->build();
        $event = new ImageDescriptionUpdated($image, 'foo');

        self::assertSame($image, $event->image);
        self::assertSame('foo', $event->oldDescription);
    }
}
