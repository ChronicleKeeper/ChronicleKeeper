<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Chat\Application\Command;

use ChronicleKeeper\Chat\Application\Command\DeleteConversation;
use ChronicleKeeper\Chat\Application\Command\DeleteConversationHandler;
use ChronicleKeeper\Chat\Domain\Event\ConversationDeleted;
use ChronicleKeeper\Shared\Infrastructure\Database\DatabasePlatform;
use ChronicleKeeper\Test\Chat\Domain\Entity\ConversationBuilder;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(DeleteConversationHandler::class)]
#[CoversClass(DeleteConversation::class)]
#[Small]
class DeleteConversationHandlerTest extends TestCase
{
    #[Test]
    public function executeDeletion(): void
    {
        $conversation = (new ConversationBuilder())->build();

        $databasePlatform = $this->createMock(DatabasePlatform::class);
        $databasePlatform->expects($this->once())
            ->method('query')
            ->with(
                'DELETE FROM conversation_messages WHERE conversation_id = :conversation_id',
                ['conversation_id' => $conversation->getId()],
            );

        $handler       = new DeleteConversationHandler($databasePlatform);
        $messageResult = $handler(new DeleteConversation($conversation));

        self::assertCount(1, $messageResult->getEvents());
        self::assertInstanceOf(ConversationDeleted::class, $messageResult->getEvents()[0]);
        self::assertSame($conversation, $messageResult->getEvents()[0]->conversation);
    }
}
