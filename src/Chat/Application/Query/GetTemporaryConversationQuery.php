<?php

declare(strict_types=1);

namespace ChronicleKeeper\Chat\Application\Query;

use ChronicleKeeper\Chat\Application\Command\StoreTemporaryConversation;
use ChronicleKeeper\Chat\Domain\Entity\Conversation;
use ChronicleKeeper\Chat\Infrastructure\Serializer\ExtendedMessageDenormalizer;
use ChronicleKeeper\Settings\Application\SettingsHandler;
use ChronicleKeeper\Shared\Application\Query\Query;
use ChronicleKeeper\Shared\Application\Query\QueryParameters;
use ChronicleKeeper\Shared\Infrastructure\Persistence\Filesystem\Contracts\FileAccess;
use ChronicleKeeper\Shared\Infrastructure\Persistence\Filesystem\Exception\UnableToReadFile;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\SerializerInterface;

use function assert;

class GetTemporaryConversationQuery implements Query
{
    public function __construct(
        private readonly FileAccess $fileAccess,
        private readonly SerializerInterface $serializer,
        private readonly MessageBusInterface $bus,
        private readonly SettingsHandler $settingsHandler,
    ) {
    }

    public function query(QueryParameters $parameters): Conversation
    {
        assert($parameters instanceof GetTemporaryConversationParameters);

        $settings                = $this->settingsHandler->get();
        $showReferencedDocuments = $settings->getChatbotGeneral()->showReferencedDocuments();
        $showReferencedImages    = $settings->getChatbotGeneral()->showReferencedImages();
        $showDebugOutput         = $settings->getChatbotFunctions()->isAllowDebugOutput();

        try {
            return $this->serializer->deserialize(
                $this->fileAccess->read('temp', 'conversation_temporary.json'),
                Conversation::class,
                JsonEncoder::FORMAT,
                [
                    ExtendedMessageDenormalizer::WITH_CONTEXT_DOCUMENTS => $showReferencedDocuments,
                    ExtendedMessageDenormalizer::WITH_CONTEXT_IMAGES => $showReferencedImages,
                    ExtendedMessageDenormalizer::WITH_DEBUG_FUNCTIONS => $showDebugOutput,
                ],
            );
        } catch (UnableToReadFile) {
            // All is fine, file not exists ... we create it.

            $conversation = Conversation::createFromSettings($settings);
            $this->bus->dispatch(new StoreTemporaryConversation($conversation));

            return $conversation;
        }
    }
}
