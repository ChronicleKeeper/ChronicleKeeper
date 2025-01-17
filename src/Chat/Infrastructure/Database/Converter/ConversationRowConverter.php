<?php

declare(strict_types=1);

namespace ChronicleKeeper\Chat\Infrastructure\Database\Converter;

use ChronicleKeeper\Chat\Domain\Entity\Conversation;
use ChronicleKeeper\Shared\Infrastructure\Database\Converter\RowConverter;
use ChronicleKeeper\Shared\Infrastructure\Database\DatabasePlatform;

use function json_decode;

use const JSON_THROW_ON_ERROR;

class ConversationRowConverter implements RowConverter
{
    public function __construct(
        private readonly DatabasePlatform $databasePlatform,
    ) {
    }

    public function getSupportedClass(): string
    {
        return Conversation::class;
    }

    /** @inheritDoc */
    public function convert(array $data): array
    {
        $settings = $this->databasePlatform->fetch(
            'SELECT * FROM conversation_settings WHERE conversation_id = :id',
            ['id' => $data['id']],
        )[0];

        $conversation = [
            'id' => $data['id'],
            'title' => $data['title'],
            'directory' => $data['directory'],
            'settings' => [
                'version' => $settings['version'],
                'temperature' => $settings['temperature'],
                'imagesMaxDistance' => $settings['images_max_distance'],
                'documentsMaxDistance' => $settings['documents_max_distance'],
            ],
            'messages' => [],
        ];

        $rawMessages = $this->databasePlatform->fetch(
            'SELECT * FROM conversation_messages WHERE conversation_id = :id',
            ['id' => $data['id']],
        );

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
