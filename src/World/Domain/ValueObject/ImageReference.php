<?php

declare(strict_types=1);

namespace ChronicleKeeper\World\Domain\ValueObject;

use ChronicleKeeper\Image\Domain\Entity\Image;
use ChronicleKeeper\World\Domain\Entity\Item;

class ImageReference implements MediaReference
{
    public function __construct(
        public readonly Item $item,
        public readonly Image $image,
    ) {
    }

    public function getType(): string
    {
        return 'image';
    }

    public function getIcon(): string
    {
        return 'tabler:photo';
    }

    public function getMediaId(): string
    {
        return $this->image->getId();
    }

    public function getMediaTitle(): string
    {
        return $this->image->getTitle();
    }

    public function getMediaDisplayName(): string
    {
        return $this->image->getDirectory()->flattenHierarchyTitle(true) . ' > ' . $this->image->getTitle();
    }

    public function getGenericLinkIdentifier(): string
    {
        return 'image_' . $this->image->getId();
    }

    /** @return array{type: string, image_id: string} */
    public function jsonSerialize(): array
    {
        return [
            'type' => $this->getType(),
            'image_id' => $this->image->getId(),
        ];
    }
}
