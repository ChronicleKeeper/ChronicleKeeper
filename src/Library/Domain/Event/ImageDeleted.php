<?php

declare(strict_types=1);

namespace ChronicleKeeper\Library\Domain\Event;

use ChronicleKeeper\Library\Domain\Entity\Image;

final readonly class ImageDeleted
{
    public function __construct(
        public Image $image,
    ) {
    }
}
