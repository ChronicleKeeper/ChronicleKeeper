<?php

declare(strict_types=1);

namespace ChronicleKeeper\Chat\Application\Query;

use ChronicleKeeper\Chat\Domain\Entity\Conversation;
use ChronicleKeeper\Shared\Application\Query\Query;
use ChronicleKeeper\Shared\Application\Query\QueryParameters;
use ChronicleKeeper\Shared\Infrastructure\Database\DatabasePlatform;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

use function assert;
use function json_decode;

class FindLatestConversationsQuery implements Query
{
    public function __construct(
        private readonly DenormalizerInterface $denormalizer,
        private readonly DatabasePlatform $databasePlatform,
    ) {
    }

    /** @return list<Conversation> */
    public function query(QueryParameters $parameters): array
    {
        assert($parameters instanceof FindLatestConversationsParameters);

        $data = $this->databasePlatform->fetch(
            'SELECT * FROM conversations ORDER BY title LIMIT :limit',
            ['limit' => $parameters->maxEntries],
        );

        $conversations = [];
        foreach ($data as $conversation) {
            $conversation['settings'] = $this->databasePlatform->fetch(
                'SELECT * FROM conversation_settings WHERE conversation_id = :id',
                ['id' => $conversation['id']],
            )[0];
            $conversation['messages'] = $this->databasePlatform->fetch(
                'SELECT * FROM conversation_messages WHERE conversation_id = :id',
                ['id' => $conversation['id']],
            );

            $conversation = $this->formatConversationFromDatabaseToArray($conversation);

            $conversations[] = $this->denormalizer->denormalize($conversation, Conversation::class);
        }

        return $conversations;
    }

    /**
     * @param array<string, mixed> $rawConversation
     *
     * @return array<string, mixed>
     */
    private function formatConversationFromDatabaseToArray(array $rawConversation): array
    {
        $conversation = [
            'id' => $rawConversation['id'],
            'title' => $rawConversation['title'],
            'directory' => $rawConversation['directory'],
            'settings' => [
                'version' => $rawConversation['settings']['version'],
                'temperature' => $rawConversation['settings']['temperature'],
                'imagesMaxDistance' => $rawConversation['settings']['images_max_distance'],
                'documentsMaxDistance' => $rawConversation['settings']['documents_max_distance'],
            ],
            'messages' => [],
        ];

        foreach ($rawConversation['messages'] as $rawMessage) {
            $conversation['messages'][] = [
                'id' => $rawMessage['id'],
                'message' => [
                    'role' => $rawMessage['role'],
                    'content' => $rawMessage['content'],
                ],
                'context' => json_decode((string) $rawMessage['context'], true),
                'debug' => json_decode((string) $rawMessage['debug'], true),
            ];
        }

        return $conversation;
    }
}
