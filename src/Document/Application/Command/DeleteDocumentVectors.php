<?php

declare(strict_types=1);

namespace ChronicleKeeper\Document\Application\Command;

use Webmozart\Assert\Assert;

class DeleteDocumentVectors
{
    public function __construct(
        public readonly string $documentId,
    ) {
        Assert::uuid($documentId);
    }
}
