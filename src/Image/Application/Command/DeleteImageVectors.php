<?php

declare(strict_types=1);

namespace ChronicleKeeper\Image\Application\Command;

class DeleteImageVectors
{
    public function __construct(
        public readonly string $imageId,
    ) {
    }
}
