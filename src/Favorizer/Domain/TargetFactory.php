<?php

declare(strict_types=1);

namespace ChronicleKeeper\Favorizer\Domain;

use ChronicleKeeper\Chat\Application\Query\FindConversationByIdParameters;
use ChronicleKeeper\Chat\Domain\Entity\Conversation;
use ChronicleKeeper\Document\Application\Query\GetDocument;
use ChronicleKeeper\Document\Domain\Entity\Document;
use ChronicleKeeper\Favorizer\Domain\Exception\UnknownMedium;
use ChronicleKeeper\Favorizer\Domain\ValueObject\ChatConversationTarget;
use ChronicleKeeper\Favorizer\Domain\ValueObject\LibraryDocumentTarget;
use ChronicleKeeper\Favorizer\Domain\ValueObject\LibraryImageTarget;
use ChronicleKeeper\Favorizer\Domain\ValueObject\Target;
use ChronicleKeeper\Favorizer\Domain\ValueObject\WorldItemTarget;
use ChronicleKeeper\Image\Application\Query\GetImage;
use ChronicleKeeper\Image\Domain\Entity\Image;
use ChronicleKeeper\Shared\Application\Query\QueryService;
use ChronicleKeeper\World\Application\Query\GetWorldItem;
use ChronicleKeeper\World\Domain\Entity\Item;

class TargetFactory
{
    public function __construct(
        private readonly QueryService $queryService,
    ) {
    }

    public function create(string $id, string $type): Target
    {
        if ($type === Document::class) {
            return $this->createFromDocument($this->queryService->query(new GetDocument($id)));
        }

        if ($type === Image::class) {
            return $this->createFromImage($this->queryService->query(new GetImage($id)));
        }

        if ($type === Conversation::class) {
            return $this->createFromConversation($this->queryService->query(new FindConversationByIdParameters($id)));
        }

        if ($type === Item::class) {
            return $this->createFromItem($this->queryService->query(new GetWorldItem($id)));
        }

        throw UnknownMedium::forType($type);
    }

    private function createFromDocument(Document $document): Target
    {
        return new LibraryDocumentTarget($document->getId(), $document->getTitle());
    }

    private function createFromImage(Image $image): Target
    {
        return new LibraryImageTarget($image->getId(), $image->getTitle());
    }

    private function createFromConversation(Conversation $conversation): Target
    {
        return new ChatConversationTarget($conversation->getId(), $conversation->getTitle());
    }

    private function createFromItem(Item $item): Target
    {
        return new WorldItemTarget($item->getId(), $item->getName());
    }
}
