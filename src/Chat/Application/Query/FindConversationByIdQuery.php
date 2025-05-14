<?php

declare(strict_types=1);

namespace ChronicleKeeper\Chat\Application\Query;

use ChronicleKeeper\Chat\Domain\Entity\Conversation;
use ChronicleKeeper\Chat\Infrastructure\Serializer\ExtendedMessageDenormalizer;
use ChronicleKeeper\Settings\Application\SettingsHandler;
use ChronicleKeeper\Shared\Application\Query\Query;
use ChronicleKeeper\Shared\Application\Query\QueryParameters;
use ChronicleKeeper\Shared\Infrastructure\Database\Converter\DatabaseRowConverter;
use Doctrine\DBAL\Connection;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

use function assert;

class FindConversationByIdQuery implements Query
{
    public function __construct(
        private readonly DenormalizerInterface $denormalizer,
        private readonly SettingsHandler $settingsHandler,
        private readonly DatabaseRowConverter $databaseRowConverter,
        private readonly Connection $connection,
    ) {
    }

    public function query(QueryParameters $parameters): Conversation|null
    {
        assert($parameters instanceof FindConversationByIdParameters);

        $data = $this->connection->createQueryBuilder()
            ->select('*')
            ->from('conversations')
            ->where('id = :id')
            ->setParameter('id', $parameters->id)
            ->fetchAssociative();

        if ($data === false) {
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
