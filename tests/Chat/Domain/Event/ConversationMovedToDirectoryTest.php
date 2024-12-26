<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Chat\Domain\Event;

use ChronicleKeeper\Chat\Domain\Event\ConversationMovedToDirectory;
use ChronicleKeeper\Test\Chat\Domain\Entity\ConversationBuilder;
use ChronicleKeeper\Test\Library\Domain\Entity\DirectoryBuilder;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(ConversationMovedToDirectory::class)]
#[Small]
class ConversationMovedToDirectoryTest extends TestCase
{
    #[Test]
    public function itCanBeCreated(): void
    {
        $conversation = (new ConversationBuilder())->build();
        $oldDirectory = (new DirectoryBuilder())->build();
        $event        = new ConversationMovedToDirectory($conversation, $oldDirectory);

        self::assertSame($conversation, $event->conversation);
        self::assertSame($oldDirectory, $event->oldDirectory);
    }
}
