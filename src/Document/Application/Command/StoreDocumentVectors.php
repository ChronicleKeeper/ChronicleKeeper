<?php

declare(strict_types=1);

namespace ChronicleKeeper\Document\Application\Command;

use ChronicleKeeper\Document\Domain\Entity\VectorDocument;

class StoreDocumentVectors
{
    public function __construct(
        public readonly VectorDocument $vectorDocument,
    ) {
    }
}
