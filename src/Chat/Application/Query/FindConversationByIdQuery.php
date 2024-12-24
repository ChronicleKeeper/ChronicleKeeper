<?php

declare(strict_types=1);

namespace ChronicleKeeper\Chat\Application\Query;

use ChronicleKeeper\Chat\Domain\Entity\Conversation;
use ChronicleKeeper\Chat\Infrastructure\Serializer\ExtendedMessageDenormalizer;
use ChronicleKeeper\Settings\Application\SettingsHandler;
use ChronicleKeeper\Shared\Application\Query\Query;
use ChronicleKeeper\Shared\Application\Query\QueryParameters;
use ChronicleKeeper\Shared\Infrastructure\Persistence\Filesystem\Contracts\FileAccess;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\SerializerInterface;

use function assert;

class FindConversationByIdQuery implements Query
{
    public function __construct(
        private readonly FileAccess $fileAccess,
        private readonly SerializerInterface $serializer,
        private readonly SettingsHandler $settingsHandler,
    ) {
    }

    public function query(QueryParameters $parameters): Conversation|null
    {
        assert($parameters instanceof FindConversationByIdParameters);

        $settings                = $this->settingsHandler->get();
        $showReferencedDocuments = $settings->getChatbotGeneral()->showReferencedDocuments();
        $showReferencedImages    = $settings->getChatbotGeneral()->showReferencedImages();
        $showDebugOutput         = $settings->getChatbotFunctions()->isAllowDebugOutput();

        $filename = $parameters->id . '.json';
        if (! $this->fileAccess->exists('library.conversations', $filename)) {
            return null;
        }

        return $this->serializer->deserialize(
            $this->fileAccess->read('library.conversations', $filename),
            Conversation::class,
            JsonEncoder::FORMAT,
            [
                ExtendedMessageDenormalizer::WITH_CONTEXT_DOCUMENTS => $showReferencedDocuments,
                ExtendedMessageDenormalizer::WITH_CONTEXT_IMAGES => $showReferencedImages,
                ExtendedMessageDenormalizer::WITH_DEBUG_FUNCTIONS => $showDebugOutput,
            ],
        );
    }
}
