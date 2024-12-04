<?php

declare(strict_types=1);

namespace ChronicleKeeper\Document\Domain\Entity;

class SearchVector
{
    /** @param list<float> $vectors */
    public function __construct(
        public readonly string $id,
        public readonly string $documentId,
        public readonly array $vectors,
    ) {
    }

    public static function fromVectorDocument(VectorDocument $vectorDocument): self
    {
        return new self(
            $vectorDocument->id,
            $vectorDocument->document->id,
            $vectorDocument->vector,
        );
    }
}
