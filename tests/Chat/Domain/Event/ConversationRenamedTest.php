<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Chat\Domain\Event;

use ChronicleKeeper\Chat\Domain\Event\ConversationRenamed;
use ChronicleKeeper\Test\Chat\Domain\Entity\ConversationBuilder;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(ConversationRenamed::class)]
#[Small]
class ConversationRenamedTest extends TestCase
{
    #[Test]
    public function itCanBeCreated(): void
    {
        $conversation = (new ConversationBuilder())->build();
        $oldTitle     = 'Old Title';
        $event        = new ConversationRenamed($conversation, $oldTitle);

        self::assertSame($conversation, $event->conversation);
        self::assertSame($oldTitle, $event->oldTitle);
    }
}
