<?php

declare(strict_types=1);

namespace ChronicleKeeper\Image\Application\Command;

use ChronicleKeeper\Image\Domain\Entity\Image;

readonly class StoreImage
{
    public function __construct(
        public Image $image,
    ) {
    }
}
