<?php

declare(strict_types=1);

namespace ChronicleKeeper\Chat\Domain\ValueObject;

use ChronicleKeeper\Document\Domain\Entity\Document;
use ChronicleKeeper\Image\Domain\Entity\Image;

class Reference
{
    public const string TYPE_DOCUMENT = 'document';
    public const string TYPE_IMAGE    = 'image';

    public function __construct(
        public readonly string $id,
        public readonly string $type,
        public readonly string $title,
    ) {
    }

    public static function forDocument(Document $document): Reference
    {
        return new self($document->getId(), self::TYPE_DOCUMENT, $document->getTitle());
    }

    public static function forImage(Image $image): Reference
    {
        return new self($image->getId(), self::TYPE_IMAGE, $image->getTitle());
    }
}
