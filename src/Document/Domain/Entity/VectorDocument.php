<?php

declare(strict_types=1);

namespace ChronicleKeeper\Document\Domain\Entity;

use JsonSerializable;
use Symfony\Component\Uid\Uuid;

/**
 * @phpstan-type VectorDocumentArray = array{
 *     id: string,
 *     documentId: string,
 *     content: string,
 *     vectorContentHash: string,
 *     vector: list<float>
 * }
 */
class VectorDocument implements JsonSerializable
{
    public string $id;

    /** @param list<float> $vector */
    public function __construct(
        public Document $document,
        public string $content,
        public string $vectorContentHash,
        public array $vector,
    ) {
        $this->id = Uuid::v4()->toString();
    }

    public function toSearchVector(): SearchVector
    {
        return SearchVector::fromVectorDocument($this);
    }

    /** @return VectorDocumentArray */
    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id,
            'documentId' => $this->document->id,
            'content' => $this->content,
            'vectorContentHash' => $this->vectorContentHash,
            'vector' => $this->vector,
        ];
    }
}
