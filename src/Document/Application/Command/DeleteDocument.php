<?php

declare(strict_types=1);

namespace ChronicleKeeper\Document\Application\Command;

use ChronicleKeeper\Document\Domain\Entity\Document;

class DeleteDocument
{
    public function __construct(
        public readonly Document $document,
    ) {
    }
}
