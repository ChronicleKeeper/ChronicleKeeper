<?php

declare(strict_types=1);

namespace ChronicleKeeper\Library\Domain\ValueObject\DirectoryContent;

use ChronicleKeeper\Chat\Domain\Entity\Conversation;
use ChronicleKeeper\Document\Domain\Entity\Document;
use ChronicleKeeper\Image\Domain\Entity\Image;
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
            $image->getId(),
            'image',
            $image->getTitle(),
            $image->getSlug(),
            $image->getSize(),
            $image->getUpdatedAt(),
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
