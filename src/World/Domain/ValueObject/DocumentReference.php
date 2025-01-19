<?php

declare(strict_types=1);

namespace ChronicleKeeper\World\Domain\ValueObject;

use ChronicleKeeper\Document\Domain\Entity\Document;
use ChronicleKeeper\World\Domain\Entity\Item;

class DocumentReference implements MediaReference
{
    public function __construct(
        public readonly Item $item,
        public readonly Document $document,
    ) {
    }

    public function getType(): string
    {
        return 'document';
    }

    public function getIcon(): string
    {
        return 'tabler:file';
    }

    public function getMediaId(): string
    {
        return $this->document->getId();
    }

    public function getMediaTitle(): string
    {
        return $this->document->getTitle();
    }

    public function getMediaDisplayName(): string
    {
        return $this->document->getDirectory()->flattenHierarchyTitle(true) . ' > ' . $this->document->getTitle();
    }

    public function getGenericLinkIdentifier(): string
    {
        return 'document_' . $this->document->getId();
    }
}
