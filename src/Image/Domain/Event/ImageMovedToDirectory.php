<?php

declare(strict_types=1);

namespace ChronicleKeeper\Image\Domain\Event;

use ChronicleKeeper\Image\Domain\Entity\Image;
use ChronicleKeeper\Library\Domain\Entity\Directory;

final readonly class ImageMovedToDirectory
{
    public function __construct(
        public Image $image,
        public Directory $oldDirectory,
    ) {
    }
}
