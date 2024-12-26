<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Library\Domain\Event;

use ChronicleKeeper\Library\Domain\Event\DirectoryRenamed;
use ChronicleKeeper\Test\Library\Domain\Entity\DirectoryBuilder;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(DirectoryRenamed::class)]
#[Small]
final class DirectoryRenamedTest extends TestCase
{
    #[Test]
    public function itCanBeCreated(): void
    {
        $directory = (new DirectoryBuilder())->build();
        $event     = new DirectoryRenamed($directory, 'old title');

        self::assertSame($directory, $event->directory);
        self::assertSame('old title', $event->oldTitle);
    }
}
