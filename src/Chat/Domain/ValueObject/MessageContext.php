<?php

declare(strict_types=1);

namespace ChronicleKeeper\Chat\Domain\ValueObject;

class MessageContext
{
    /**
     * @param list<Reference> $documents
     * @param list<Reference> $images
     */
    public function __construct(
        public readonly array $documents = [],
        public readonly array $images = [],
    ) {
    }
}
