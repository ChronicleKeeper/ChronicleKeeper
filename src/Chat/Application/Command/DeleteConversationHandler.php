<?php

declare(strict_types=1);

namespace ChronicleKeeper\Chat\Application\Command;

use ChronicleKeeper\Chat\Domain\Event\ConversationDeleted;
use ChronicleKeeper\Shared\Infrastructure\Messenger\MessageEventResult;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class DeleteConversationHandler
{
    public function __construct(
        private readonly Connection $connection,
    ) {
    }

    public function __invoke(DeleteConversation $message): MessageEventResult
    {
        try {
            $this->connection->beginTransaction();

            // Delete conversation settings
            $this->connection->createQueryBuilder()
                ->delete('conversation_settings')
                ->where('conversation_id = :conversationId')
                ->setParameter('conversationId', $message->conversation->getId())
                ->executeStatement();

            // Delete conversation messages
            $this->connection->createQueryBuilder()
                ->delete('conversation_messages')
                ->where('conversation_id = :conversationId')
                ->setParameter('conversationId', $message->conversation->getId())
                ->executeStatement();

            // Delete conversation
            $this->connection->createQueryBuilder()
                ->delete('conversations')
                ->where('id = :conversationId')
                ->setParameter('conversationId', $message->conversation->getId())
                ->executeStatement();

            $this->connection->commit();
        } catch (Exception $e) {
            $this->connection->rollBack();

            throw $e;
        }

        return new MessageEventResult([new ConversationDeleted($message->conversation)]);
    }
}
