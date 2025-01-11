<?php

declare(strict_types=1);

namespace ChronicleKeeper\Document\Application\Command;

class DeleteDocumentVectors
{
    public function __construct(
        public readonly string $documentId,
    ) {
    }
}
