<?php

declare(strict_types=1);

namespace ChronicleKeeper\Library\Domain\Event;

use ChronicleKeeper\Library\Domain\Entity\Directory;

final readonly class DirectoryDeleted
{
    public function __construct(
        public Directory $directory,
    ) {
    }
}
