<?php

declare(strict_types=1);

namespace ChronicleKeeper\World\Application\Query;

use ChronicleKeeper\Chat\Application\Query\FindConversationByIdParameters;
use ChronicleKeeper\Document\Application\Query\GetDocument;
use ChronicleKeeper\Image\Application\Query\GetImage;
use ChronicleKeeper\Shared\Application\Query\Query;
use ChronicleKeeper\Shared\Application\Query\QueryParameters;
use ChronicleKeeper\Shared\Application\Query\QueryService;
use ChronicleKeeper\Shared\Infrastructure\Database\DatabasePlatform;
use ChronicleKeeper\World\Domain\Entity\Item;
use ChronicleKeeper\World\Domain\ValueObject\ConversationReference;
use ChronicleKeeper\World\Domain\ValueObject\DocumentReference;
use ChronicleKeeper\World\Domain\ValueObject\ImageReference;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Throwable;

use function assert;

class GetWorldItemQuery implements Query
{
    public function __construct(
        private readonly DenormalizerInterface $denormalizer,
        private readonly DatabasePlatform $databasePlatform,
        private readonly QueryService $queryService,
    ) {
    }

    public function query(QueryParameters $parameters): Item
    {
        assert($parameters instanceof GetWorldItem);

        $document = $this->databasePlatform->fetchSingleRow(
            'SELECT id, type, name, short_description as shortDescription FROM world_items WHERE id = :id',
            ['id' => $parameters->id],
        );

        $item = $this->denormalizer->denormalize($document, Item::class);
        $this->addDocumentReferences($item);
        $this->addImageReferences($item);
        $this->addConversationReferences($item);

        return $item;
    }

    private function addDocumentReferences(Item $item): void
    {
        $documentReferences = $this->databasePlatform->fetch(
            'SELECT document_id FROM world_item_documents WHERE world_item_id = :id',
            ['id' => $item->getId()],
        );

        foreach ($documentReferences as $documentReference) {
            try {
                $document = $this->queryService->query(new GetDocument($documentReference['document_id']));
                $item->addMediaReference(new DocumentReference($item, $document));
            } catch (Throwable) {
                // It is aceptable if a reference is defect ...
                continue;
            }
        }
    }

    private function addImageReferences(Item $item): void
    {
        $imageReferences = $this->databasePlatform->fetch(
            'SELECT image_id FROM world_item_images WHERE world_item_id = :id',
            ['id' => $item->getId()],
        );

        foreach ($imageReferences as $imageReference) {
            try {
                $image = $this->queryService->query(new GetImage($imageReference['image_id']));
                $item->addMediaReference(new ImageReference($item, $image));
            } catch (Throwable) {
                // It is aceptable if a reference is defect ...
                continue;
            }
        }
    }

    private function addConversationReferences(Item $item): void
    {
        $conversationReferences = $this->databasePlatform->fetch(
            'SELECT conversation_id FROM world_item_conversations WHERE world_item_id = :id',
            ['id' => $item->getId()],
        );

        foreach ($conversationReferences as $conversationReference) {
            try {
                $conversation = $this->queryService->query(new FindConversationByIdParameters($conversationReference['conversation_id']));
                $item->addMediaReference(new ConversationReference($item, $conversation));
            } catch (Throwable) {
                // It is aceptable if a reference is defect ...
                continue;
            }
        }
    }
}
