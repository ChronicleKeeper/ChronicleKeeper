<?php

declare(strict_types=1);

namespace ChronicleKeeper\Library\Domain\ValueObject\DirectoryCache;

use ChronicleKeeper\Chat\Domain\Entity\Conversation;
use ChronicleKeeper\Document\Domain\Entity\Document;
use ChronicleKeeper\Library\Domain\Entity\Image;
use DateTimeImmutable;

class Element
{
    public function __construct(
        public string $id,
        public string $type,
        public string $title,
        public string $slug,
        public int|null $size = 0,
        public DateTimeImmutable|null $updatedAt = null,
    ) {
        $this->updatedAt ??= new DateTimeImmutable();
    }

    public static function fromDocumentEntity(Document $document): Element
    {
        return new Element(
            $document->getId(),
            'document',
            $document->getTitle(),
            $document->getSlug(),
            $document->getSize(),
            $document->getUpdatedAt(),
        );
    }

    public static function fromImageEntity(Image $image): Element
    {
        return new Element(
            $image->id,
            'image',
            $image->title,
            $image->getSlug(),
            $image->getSize(),
            $image->updatedAt,
        );
    }

    public static function fromConversationEntity(Conversation $conversation): Element
    {
        return new Element(
            $conversation->getId(),
            'conversation',
            $conversation->getTitle(),
            $conversation->getSlug(),
            $conversation->getMessages()->count(),
            null,
        );
    }
}
