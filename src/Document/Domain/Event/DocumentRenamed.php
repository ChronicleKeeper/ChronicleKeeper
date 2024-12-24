<?php

declare(strict_types=1);

namespace ChronicleKeeper\Document\Domain\Event;

use ChronicleKeeper\Document\Domain\Entity\Document;

final readonly class DocumentRenamed
{
    public function __construct(
        public Document $document,
        public string $oldTitle,
    ) {
    }
}
