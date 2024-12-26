<?php

declare(strict_types=1);

namespace Chat\Domain\Event;

use ChronicleKeeper\Chat\Domain\Event\ConversationDeleted;
use ChronicleKeeper\Test\Chat\Domain\Entity\ConversationBuilder;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(ConversationDeleted::class)]
#[Small]
class ConversationDeletedTest extends TestCase
{
    #[Test]
    public function itCanBeCreated(): void
    {
        $conversation = (new ConversationBuilder())->build();
        $event        = new ConversationDeleted($conversation);

        self::assertSame($conversation, $event->conversation);
    }
}
