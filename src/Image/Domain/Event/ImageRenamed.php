<?php

declare(strict_types=1);

namespace ChronicleKeeper\Image\Domain\Event;

use ChronicleKeeper\Image\Domain\Entity\Image;

final readonly class ImageRenamed
{
    public function __construct(
        public Image $image,
        public string $oldTitle,
    ) {
    }
}
