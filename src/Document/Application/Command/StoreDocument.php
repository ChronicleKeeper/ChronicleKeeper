<?php

declare(strict_types=1);

namespace ChronicleKeeper\Document\Application\Command;

use ChronicleKeeper\Library\Domain\Entity\Document;

class StoreDocument
{
    public function __construct(
        public readonly Document $document,
        public readonly bool $updateTimestamp = true,
    ) {
    }
}
