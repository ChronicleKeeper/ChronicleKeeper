<?php

declare(strict_types=1);

namespace ChronicleKeeper\ImageGenerator\Domain\ValueObject;

use ChronicleKeeper\Library\Domain\Entity\Image;
use JsonSerializable;

class GeneratorResult implements JsonSerializable
{
    public function __construct(
        public string $encodedImage,
        public Image|null $image = null, // Not null when already taken to library
        public string $mimeType = 'image/png',
    ) {
    }

    public function getImageUrl(): string
    {
        return 'data:' . $this->mimeType . ';base64,' . $this->encodedImage;
    }

    /** @return array{encodedImage: string, mimeType: string, image: string|null} */
    public function jsonSerialize(): array
    {
        return [
            'encodedImage' => $this->encodedImage,
            'mimeType' => $this->mimeType,
            'image' => $this->image?->id,
        ];
    }
}
