<?php

declare(strict_types=1);

namespace ChronicleKeeper\Image\Domain\Event;

use ChronicleKeeper\Image\Domain\Entity\Image;

final readonly class ImageCreated
{
    public function __construct(
        public Image $image,
    ) {
    }
}
