<?php

declare(strict_types=1);

namespace ChronicleKeeper\Document\Application\Command;

use ChronicleKeeper\Library\Infrastructure\VectorStorage\VectorDocument;

class StoreDocumentVectors
{
    public function __construct(
        public readonly VectorDocument $vectorDocument,
    ) {
    }
}
