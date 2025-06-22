<?php

declare(strict_types=1);

namespace ChronicleKeeper\Chat\Application\Command;

use ChronicleKeeper\Shared\Infrastructure\Messenger\MessageEventResult;
use Doctrine\DBAL\Connection;
use PhpLlm\LlmChain\Platform\Message\AssistantMessage;
use PhpLlm\LlmChain\Platform\Message\Content\Text;
use PhpLlm\LlmChain\Platform\Message\SystemMessage;
use PhpLlm\LlmChain\Platform\Message\UserMessage;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Throwable;

use function assert;
use function json_encode;

use const JSON_THROW_ON_ERROR;

#[AsMessageHandler]
class StoreConversationHandler
{
    public function __construct(
        private readonly Connection $connection,
    ) {
    }

    public function __invoke(StoreConversation $message): MessageEventResult
    {
        try {
            $this->connection->beginTransaction();

            // Handle conversation upsert using query builder
            $conversationExists = $this->connection->createQueryBuilder()
                ->select('1')
                ->from('conversations')
                ->where('id = :id')
                ->setParameter('id', $message->conversation->getId())
                ->executeQuery()
                ->fetchOne();

            if ($conversationExists !== false) {
                // Update existing conversation
                $this->connection->createQueryBuilder()
                    ->update('conversations')
                    ->set('title', ':title')
                    ->set('directory', ':directory')
                    ->where('id = :id')
                    ->setParameters([
                        'id' => $message->conversation->getId(),
                        'title' => $message->conversation->getTitle(),
                        'directory' => $message->conversation->getDirectory()->getId(),
                    ])
                    ->executeStatement();
            } else {
                // Insert new conversation
                $this->connection->createQueryBuilder()
                    ->insert('conversations')
                    ->values([
                        'id' => ':id',
                        'title' => ':title',
                        'directory' => ':directory',
                    ])
                    ->setParameters([
                        'id' => $message->conversation->getId(),
                        'title' => $message->conversation->getTitle(),
                        'directory' => $message->conversation->getDirectory()->getId(),
                    ])
                    ->executeStatement();
            }

            // Handle conversation settings upsert
            $settingsExists = $this->connection->createQueryBuilder()
                ->select('1')
                ->from('conversation_settings')
                ->where('conversation_id = :conversationId')
                ->setParameter('conversationId', $message->conversation->getId())
                ->executeQuery()
                ->fetchOne();

            if ($settingsExists !== false) {
                // Update existing settings
                $this->connection->createQueryBuilder()
                    ->update('conversation_settings')
                    ->set('version', ':version')
                    ->set('temperature', ':temperature')
                    ->set('images_max_distance', ':images_max_distance')
                    ->set('documents_max_distance', ':documents_max_distance')
                    ->where('conversation_id = :conversation_id')
                    ->setParameters([
                        'conversation_id' => $message->conversation->getId(),
                        'version' => $message->conversation->getSettings()->version,
                        'temperature' => $message->conversation->getSettings()->temperature,
                        'images_max_distance' => $message->conversation->getSettings()->imagesMaxDistance,
                        'documents_max_distance' => $message->conversation->getSettings()->documentsMaxDistance,
                    ])
                    ->executeStatement();
            } else {
                // Insert new settings
                $this->connection->createQueryBuilder()
                    ->insert('conversation_settings')
                    ->values([
                        'conversation_id' => ':conversation_id',
                        'version' => ':version',
                        'temperature' => ':temperature',
                        'images_max_distance' => ':images_max_distance',
                        'documents_max_distance' => ':documents_max_distance',
                    ])
                    ->setParameters([
                        'conversation_id' => $message->conversation->getId(),
                        'version' => $message->conversation->getSettings()->version,
                        'temperature' => $message->conversation->getSettings()->temperature,
                        'images_max_distance' => $message->conversation->getSettings()->imagesMaxDistance,
                        'documents_max_distance' => $message->conversation->getSettings()->documentsMaxDistance,
                    ])
                    ->executeStatement();
            }

            // Delete messages using query builder
            $this->connection->createQueryBuilder()
                ->delete('conversation_messages')
                ->where('conversation_id = :conversationId')
                ->setParameter('conversationId', $message->conversation->getId())
                ->executeStatement();

            // Insert messages using query builder
            foreach ($message->conversation->getMessages() as $conversationMessage) {
                $messageArray = $conversationMessage->jsonSerialize();

                $messageContent = '';
                if ($messageArray['message'] instanceof SystemMessage || $messageArray['message'] instanceof AssistantMessage) {
                    $messageContent = (string) $messageArray['message']->content;
                } elseif ($messageArray['message'] instanceof UserMessage) {
                    $content = $messageArray['message']->content[0];
                    assert($content instanceof Text);

                    $messageContent = $content->text;
                }

                $this->connection->createQueryBuilder()
                    ->insert('conversation_messages')
                    ->values([
                        'id' => ':id',
                        'conversation_id' => ':conversationId',
                        'role' => ':role',
                        'content' => ':content',
                        'context' => ':context',
                        'debug' => ':debug',
                    ])
                    ->setParameters([
                        'id' => $messageArray['id'],
                        'conversationId' => $message->conversation->getId(),
                        'role' => $messageArray['message']->getRole()->value,
                        'content' => $messageContent,
                        'context' => json_encode($messageArray['context'], JSON_THROW_ON_ERROR),
                        'debug' => json_encode($messageArray['debug'], JSON_THROW_ON_ERROR),
                    ])
                    ->executeStatement();
            }

            $this->connection->commit();

            return new MessageEventResult($message->conversation->flushEvents());
        } catch (Throwable $exception) {
            $this->connection->rollBack();

            throw $exception;
        }
    }
}
