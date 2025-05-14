<?php

declare(strict_types=1);

namespace ChronicleKeeper\Chat\Infrastructure\Database\Converter;

use ChronicleKeeper\Chat\Domain\Entity\Conversation;
use ChronicleKeeper\Chat\Domain\ValueObject\Settings;
use ChronicleKeeper\Shared\Infrastructure\Database\Converter\RowConverter;
use Doctrine\DBAL\Connection;

use function json_decode;

use const JSON_THROW_ON_ERROR;

class ConversationRowConverter implements RowConverter
{
    public function __construct(
        private readonly Connection $connection,
    ) {
    }

    public function getSupportedClass(): string
    {
        return Conversation::class;
    }

    /** @inheritDoc */
    public function convert(array $data): array
    {
        $settings = $this->connection->createQueryBuilder()
            ->select('*')
            ->from('conversation_settings')
            ->where('conversation_id = :conversationId')
            ->setParameter('conversationId', $data['id'])
            ->executeQuery()
            ->fetchAssociative();

        if ($settings === false) {
            $settings = (new Settings())->jsonSerialize();
        }

        $conversation = [
            'id' => $data['id'],
            'title' => $data['title'],
            'directory' => $data['directory'],
            'settings' => [
                'version' => $settings['version'],
                'temperature' => (float) $settings['temperature'],
                'imagesMaxDistance' => (float) $settings['images_max_distance'],
                'documentsMaxDistance' => (float) $settings['documents_max_distance'],
            ],
            'messages' => [],
        ];

        $rawMessages = $this->connection->createQueryBuilder()
            ->select('*')
            ->from('conversation_messages')
            ->where('conversation_id = :conversationId')
            ->setParameter('conversationId', $data['id'])
            ->executeQuery()
            ->fetchAllAssociative();

        foreach ($rawMessages as $rawMessage) {
            $conversation['messages'][] = [
                'id' => $rawMessage['id'],
                'message' => [
                    'role' => $rawMessage['role'],
                    'content' => $rawMessage['content'],
                ],
                'context' => json_decode((string) $rawMessage['context'], true, 512, JSON_THROW_ON_ERROR),
                'debug' => json_decode((string) $rawMessage['debug'], true, 512, JSON_THROW_ON_ERROR),
            ];
        }

        return $conversation;
    }
}
