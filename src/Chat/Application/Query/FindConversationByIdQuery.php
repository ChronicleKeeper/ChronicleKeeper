<?php

declare(strict_types=1);

namespace ChronicleKeeper\Chat\Application\Query;

use ChronicleKeeper\Chat\Domain\Entity\Conversation;
use ChronicleKeeper\Chat\Infrastructure\Serializer\ExtendedMessageDenormalizer;
use ChronicleKeeper\Settings\Application\SettingsHandler;
use ChronicleKeeper\Shared\Application\Query\Query;
use ChronicleKeeper\Shared\Application\Query\QueryParameters;
use ChronicleKeeper\Shared\Infrastructure\Database\Converter\DatabaseRowConverter;
use ChronicleKeeper\Shared\Infrastructure\Database\DatabasePlatform;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

use function assert;

class FindConversationByIdQuery implements Query
{
    public function __construct(
        private readonly DenormalizerInterface $denormalizer,
        private readonly SettingsHandler $settingsHandler,
        private readonly DatabasePlatform $databasePlatform,
        private readonly DatabaseRowConverter $databaseRowConverter,
    ) {
    }

    public function query(QueryParameters $parameters): Conversation|null
    {
        assert($parameters instanceof FindConversationByIdParameters);

        $data = $this->databasePlatform->createQueryBuilder()->createSelect()
            ->from('conversations')
            ->where('id', '=', $parameters->id)
            ->fetchOneOrNull();

        if ($data === null) {
            return null;
        }

        $conversation = $this->databaseRowConverter->convert($data, Conversation::class);

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
}
