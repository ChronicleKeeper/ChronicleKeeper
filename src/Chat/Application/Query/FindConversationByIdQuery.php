<?php

declare(strict_types=1);

namespace ChronicleKeeper\Chat\Application\Query;

use ChronicleKeeper\Chat\Domain\Entity\Conversation;
use ChronicleKeeper\Chat\Infrastructure\Serializer\ExtendedMessageDenormalizer;
use ChronicleKeeper\Settings\Application\SettingsHandler;
use ChronicleKeeper\Shared\Application\Query\Query;
use ChronicleKeeper\Shared\Application\Query\QueryParameters;
use ChronicleKeeper\Shared\Infrastructure\Database\DatabasePlatform;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

use function assert;
use function count;
use function json_decode;

class FindConversationByIdQuery implements Query
{
    public function __construct(
        private readonly DenormalizerInterface $denormalizer,
        private readonly SettingsHandler $settingsHandler,
        private readonly DatabasePlatform $databasePlatform,
    ) {
    }

    public function query(QueryParameters $parameters): Conversation|null
    {
        assert($parameters instanceof FindConversationByIdParameters);

        $data = $this->databasePlatform->fetch('SELECT * FROM conversations WHERE id = :id', ['id' => $parameters->id]);
        if (count($data) === 0) {
            return null;
        }

        $conversation             = $data[0];
        $conversation['settings'] = $this->databasePlatform->fetch(
            'SELECT * FROM conversation_settings WHERE conversation_id = :id',
            ['id' => $parameters->id],
        )[0];
        $conversation['messages'] = $this->databasePlatform->fetch(
            'SELECT * FROM conversation_messages WHERE conversation_id = :id',
            ['id' => $parameters->id],
        );

        $conversation = $this->formatConversationFromDatabaseToArray($conversation);

        $settings                = $this->settingsHandler->get();
        $showReferencedDocuments = $settings->getChatbotGeneral()->showReferencedDocuments();
        $showReferencedImages    = $settings->getChatbotGeneral()->showReferencedImages();
        $showDebugOutput         = $settings->getChatbotFunctions()->isAllowDebugOutput();

        return $this->denormalizer->denormalize(
            data: $conversation,
            type: Conversation::class,
            context: [
                ExtendedMessageDenormalizer::WITH_CONTEXT_DOCUMENTS => $showReferencedDocuments,
                ExtendedMessageDenormalizer::WITH_CONTEXT_IMAGES => $showReferencedImages,
                ExtendedMessageDenormalizer::WITH_DEBUG_FUNCTIONS => $showDebugOutput,
            ],
        );
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
