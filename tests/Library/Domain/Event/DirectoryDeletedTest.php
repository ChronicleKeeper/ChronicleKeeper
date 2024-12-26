<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Library\Domain\Event;

use ChronicleKeeper\Library\Domain\Event\DirectoryDeleted;
use ChronicleKeeper\Test\Library\Domain\Entity\DirectoryBuilder;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(DirectoryDeleted::class)]
#[Small]
final class DirectoryDeletedTest extends TestCase
{
    #[Test]
    public function itCanBeCreated(): void
    {
        $directory = (new DirectoryBuilder())->build();
        $event     = new DirectoryDeleted($directory);

        self::assertSame($directory, $event->directory);
    }
}
