<?php

declare(strict_types=1);

namespace ChronicleKeeper\ImageGenerator\Domain\Entity;

use ChronicleKeeper\Library\Domain\Entity\Image;
use JsonSerializable;
use Symfony\Component\Uid\Uuid;

class GeneratorResult implements JsonSerializable
{
    public string $id;

    public function __construct(
        public string $encodedImage,
        public Image|null $image = null, // Not null when already taken to library
        public string $mimeType = 'image/png',
    ) {
        $this->id = Uuid::v4()->toString();
    }

    public function getImageUrl(): string
    {
        return 'data:' . $this->mimeType . ';base64,' . $this->encodedImage;
    }

    /** @return array{id: string, encodedImage: string, mimeType: string, image: string|null} */
    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id,
            'encodedImage' => $this->encodedImage,
            'mimeType' => $this->mimeType,
            'image' => $this->image?->id,
        ];
    }
}
