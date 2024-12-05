<?php

declare(strict_types=1);

namespace ChronicleKeeper\Image\Domain\Entity;

use ChronicleKeeper\Library\Infrastructure\VectorStorage\VectorImage;

class SearchVector
{
    /** @param list<float> $vectors */
    public function __construct(
        public readonly string $id,
        public readonly string $imageId,
        public readonly array $vectors,
    ) {
    }

    public static function formVectorImage(VectorImage $vectorImage): self
    {
        return new self(
            $vectorImage->id,
            $vectorImage->image->id,
            $vectorImage->vector,
        );
    }
}
