<?php

declare(strict_types=1);

namespace ChronicleKeeper\Chat\Application\Command;

use ChronicleKeeper\Chat\Domain\Event\ConversationDeleted;
use ChronicleKeeper\Shared\Infrastructure\Database\DatabasePlatform;
use ChronicleKeeper\Shared\Infrastructure\Database\Exception\DatabaseQueryException;
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
        try {
            $this->databasePlatform->beginTransaction();

            $this->databasePlatform->createQueryBuilder()->createDelete()
                ->from('conversation_settings')
                ->where('conversation_id', '=', $message->conversation->getId())
                ->execute();

            $this->databasePlatform->createQueryBuilder()->createDelete()
                ->from('conversation_messages')
                ->where('conversation_id', '=', $message->conversation->getId())
                ->execute();

            $this->databasePlatform->createQueryBuilder()->createDelete()
                ->from('conversations')
                ->where('id', '=', $message->conversation->getId())
                ->execute();

            $this->databasePlatform->commit();
        } catch (DatabaseQueryException $e) {
            $this->databasePlatform->rollback();

            throw $e;
        }

        return new MessageEventResult([new ConversationDeleted($message->conversation)]);
    }
}
