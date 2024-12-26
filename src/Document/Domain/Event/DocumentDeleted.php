<?php

declare(strict_types=1);

namespace ChronicleKeeper\Document\Domain\Event;

use ChronicleKeeper\Document\Domain\Entity\Document;

final readonly class DocumentDeleted
{
    public function __construct(
        public Document $document,
    ) {
    }
}
