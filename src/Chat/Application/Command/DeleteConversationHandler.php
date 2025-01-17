<?php

declare(strict_types=1);

namespace ChronicleKeeper\Chat\Application\Command;

use ChronicleKeeper\Chat\Domain\Event\ConversationDeleted;
use ChronicleKeeper\Shared\Infrastructure\Database\DatabasePlatform;
use ChronicleKeeper\Shared\Infrastructure\Messenger\MessageEventResult;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class DeleteConversationHandler
{
    public function __construct(
        private readonly DatabasePlatform $databasePlatform,
    ) {
    }

    public function __invoke(DeleteConversation $message): MessageEventResult
    {
        $this->databasePlatform->query(
            'DELETE FROM conversation_messages WHERE conversation_id = :conversation_id',
            ['conversation_id' => $message->conversation->getId()],
        );

        return new MessageEventResult([new ConversationDeleted($message->conversation)]);
    }
}
