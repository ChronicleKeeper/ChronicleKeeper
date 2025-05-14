<?php

declare(strict_types=1);

namespace ChronicleKeeper\World\Application\Query;

use ChronicleKeeper\Chat\Application\Query\FindConversationByIdParameters;
use ChronicleKeeper\Document\Application\Query\GetDocument;
use ChronicleKeeper\Image\Application\Query\GetImage;
use ChronicleKeeper\Shared\Application\Query\Query;
use ChronicleKeeper\Shared\Application\Query\QueryParameters;
use ChronicleKeeper\Shared\Application\Query\QueryService;
use ChronicleKeeper\World\Domain\Entity\Item;
use ChronicleKeeper\World\Domain\ValueObject\ConversationReference;
use ChronicleKeeper\World\Domain\ValueObject\DocumentReference;
use ChronicleKeeper\World\Domain\ValueObject\ImageReference;
use Doctrine\DBAL\Connection;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Throwable;

use function assert;

class GetWorldItemQuery implements Query
{
    public function __construct(
        private readonly DenormalizerInterface $denormalizer,
        private readonly Connection $connection,
        private readonly QueryService $queryService,
    ) {
    }

    public function query(QueryParameters $parameters): Item
    {
        assert($parameters instanceof GetWorldItem);

        $itemData = $this->connection->createQueryBuilder()
            ->select('id', 'type', 'name', 'short_description AS "shortDescription"')
            ->from('world_items')
            ->where('id = :id')
            ->setParameter('id', $parameters->id)
            ->executeQuery()
            ->fetchAssociative();

        $item = $this->denormalizer->denormalize($itemData, Item::class);
        $this->addDocumentReferences($item);
        $this->addImageReferences($item);
        $this->addConversationReferences($item);

        return $item;
    }

    private function addDocumentReferences(Item $item): void
    {
        $documentReferences = $this->connection->createQueryBuilder()
            ->select('document_id')
            ->from('world_item_documents')
            ->where('world_item_id = :itemId')
            ->setParameter('itemId', $item->getId())
            ->executeQuery()
            ->fetchAllAssociative();

        foreach ($documentReferences as $documentReference) {
            try {
                $document = $this->queryService->query(new GetDocument($documentReference['document_id']));
                $item->addMediaReference(new DocumentReference($item, $document));
            } catch (Throwable) {
                // It is acceptable if a reference is defect ...
                continue;
            }
        }
    }

    private function addImageReferences(Item $item): void
    {
        $imageReferences = $this->connection->createQueryBuilder()
            ->select('image_id')
            ->from('world_item_images')
            ->where('world_item_id = :itemId')
            ->setParameter('itemId', $item->getId())
            ->executeQuery()
            ->fetchAllAssociative();

        foreach ($imageReferences as $imageReference) {
            try {
                $image = $this->queryService->query(new GetImage($imageReference['image_id']));
                $item->addMediaReference(new ImageReference($item, $image));
            } catch (Throwable) {
                // It is acceptable if a reference is defect ...
                continue;
            }
        }
    }

    private function addConversationReferences(Item $item): void
    {
        $conversationReferences = $this->connection->createQueryBuilder()
            ->select('conversation_id')
            ->from('world_item_conversations')
            ->where('world_item_id = :itemId')
            ->setParameter('itemId', $item->getId())
            ->executeQuery()
            ->fetchAllAssociative();

        foreach ($conversationReferences as $conversationReference) {
            try {
                $conversation = $this->queryService->query(
                    new FindConversationByIdParameters(
                        $conversationReference['conversation_id'],
                    ),
                );
                $item->addMediaReference(new ConversationReference($item, $conversation));
            } catch (Throwable) {
                // It is acceptable if a reference is defect ...
                continue;
            }
        }
    }
}
