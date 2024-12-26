<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Image\Domain\Event;

use ChronicleKeeper\Image\Domain\Event\ImageMovedToDirectory;
use ChronicleKeeper\Test\Image\Domain\Entity\ImageBuilder;
use ChronicleKeeper\Test\Library\Domain\Entity\DirectoryBuilder;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(ImageMovedToDirectory::class)]
#[Small]
final class ImageMovedToDirectoryTest extends TestCase
{
    #[Test]
    public function itCanBeConstructed(): void
    {
        $image        = (new ImageBuilder())->build();
        $oldDirectory = (new DirectoryBuilder())->build();
        $event        = new ImageMovedToDirectory($image, $oldDirectory);

        self::assertSame($image, $event->image);
        self::assertSame($oldDirectory, $event->oldDirectory);
    }
}
