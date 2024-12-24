<?php

declare(strict_types=1);

namespace ChronicleKeeper\Document\Domain\Event;

use ChronicleKeeper\Document\Domain\Entity\Document;
use ChronicleKeeper\Library\Domain\Entity\Directory;

final readonly class DocumentMovedToDirectory
{
    public function __construct(
        public Document $document,
        public Directory $oldDirectory,
    ) {
    }
}
