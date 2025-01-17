<?php

declare(strict_types=1);

namespace ChronicleKeeper\Image\Application\Command;

use ChronicleKeeper\Image\Domain\Entity\Image;

readonly class DeleteImage
{
    public function __construct(
        public Image $image,
    ) {
    }
}
