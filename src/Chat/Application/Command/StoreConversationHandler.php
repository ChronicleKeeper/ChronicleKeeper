<?php

declare(strict_types=1);

namespace ChronicleKeeper\Chat\Application\Command;

use ChronicleKeeper\Shared\Infrastructure\Database\DatabasePlatform;
use ChronicleKeeper\Shared\Infrastructure\Messenger\MessageEventResult;
use PhpLlm\LlmChain\Model\Message\AssistantMessage;
use PhpLlm\LlmChain\Model\Message\SystemMessage;
use PhpLlm\LlmChain\Model\Message\UserMessage;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Throwable;

use function json_encode;

use const JSON_THROW_ON_ERROR;

#[AsMessageHandler]
class StoreConversationHandler
{
    public function __construct(
        private readonly DatabasePlatform $databasePlatform,
    ) {
    }

    public function __invoke(StoreConversation $message): MessageEventResult
    {
        try {
            $this->databasePlatform->beginTransaction();

            // Insert or update the conversation itself to the conversation table
            $this->databasePlatform->insertOrUpdate(
                'conversations',
                [
                    'id' => $message->conversation->getId(),
                    'title' => $message->conversation->getTitle(),
                    'directory' => $message->conversation->getDirectory()->getId(),
                ],
            );

            // Insert or update the settings of the conversation to the settings table
            $this->databasePlatform->insertOrUpdate(
                'conversation_settings',
                [
                    'conversation_id' => $message->conversation->getId(),
                    'version' => $message->conversation->getSettings()->version,
                    'temperature' => $message->conversation->getSettings()->temperature,
                    'images_max_distance' => $message->conversation->getSettings()->imagesMaxDistance,
                    'documents_max_distance' => $message->conversation->getSettings()->documentsMaxDistance,
                ],
            );

            // Remove all messages from the conversation_messages table and insert all messages of the conversation
            $this->databasePlatform->query(
                'DELETE FROM conversation_messages WHERE conversation_id = :conversation_id',
                ['conversation_id' => $message->conversation->getId()],
            );

            foreach ($message->conversation->getMessages() as $conversationMessage) {
                $messageArray = $conversationMessage->jsonSerialize();

                $messageContent = '';
                if ($messageArray['message'] instanceof SystemMessage || $messageArray['message'] instanceof AssistantMessage) {
                    $messageContent = (string) $messageArray['message']->content;
                } elseif ($messageArray['message'] instanceof UserMessage) {
                    $messageContent = $messageArray['message']->jsonSerialize();
                    $messageContent = $messageContent['content'];
                }

                $this->databasePlatform->insert(
                    'conversation_messages',
                    [
                        'id' => $messageArray['id'],
                        'conversation_id' => $message->conversation->getId(),
                        'role' => $messageArray['message']->getRole()->value,
                        'content' => $messageContent,
                        'context' => json_encode($messageArray['context'], JSON_THROW_ON_ERROR),
                        'debug' => json_encode($messageArray['debug'], JSON_THROW_ON_ERROR),
                    ],
                );
            }

            $this->databasePlatform->commit();

            return new MessageEventResult($message->conversation->flushEvents());
        } catch (Throwable $exception) {
            $this->databasePlatform->rollback();

            throw $exception;
        }
    }
}
