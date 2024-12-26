<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Library\Domain\Event;

use ChronicleKeeper\Library\Domain\Event\DirectoryMovedToDirectory;
use ChronicleKeeper\Test\Library\Domain\Entity\DirectoryBuilder;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(DirectoryMovedToDirectory::class)]
#[Small]
final class DirectoryMovedToDirectoryTest extends TestCase
{
    #[Test]
    public function itCanBeCreated(): void
    {
        $directory    = (new DirectoryBuilder())->build();
        $oldDirectory = (new DirectoryBuilder())->build();
        $event        = new DirectoryMovedToDirectory($directory, $oldDirectory);

        self::assertSame($directory, $event->directory);
        self::assertSame($oldDirectory, $event->oldParent);
    }
}
