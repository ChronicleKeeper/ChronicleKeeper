<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Chat\Domain\Event;

use ChronicleKeeper\Chat\Domain\Event\ConversationCreated;
use ChronicleKeeper\Test\Chat\Domain\Entity\ConversationBuilder;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(ConversationCreated::class)]
#[Small]
class ConversationCreatedTest extends TestCase
{
    #[Test]
    public function itCanBeCreated(): void
    {
        $conversation = (new ConversationBuilder())->build();
        $event        = new ConversationCreated($conversation);

        self::assertSame($conversation, $event->conversation);
    }
}
