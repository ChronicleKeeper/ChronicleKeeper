<?php

declare(strict_types=1);

namespace ChronicleKeeper\Image\Application\Command;

use ChronicleKeeper\Library\Infrastructure\VectorStorage\VectorImage;

class StoreImageVectors
{
    public function __construct(
        public readonly VectorImage $vectorImage,
    ) {
    }
}
