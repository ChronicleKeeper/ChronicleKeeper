<?php

declare(strict_types=1);

namespace ChronicleKeeper\Document\Domain\Entity;

use Symfony\Component\Uid\Uuid;

use function array_key_exists;
use function count;

/**
 * @phpstan-type VectorDocumentArray = array{
 *     id: string,
 *     documentId: string,
 *     content: string,
 *     vectorContentHash: string,
 *     vector: list<float>
 * }
 */
class VectorDocument
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

    /** @return VectorDocumentArray */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'documentId' => $this->document->id,
            'content' => $this->content,
            'vectorContentHash' => $this->vectorContentHash,
            'vector' => $this->vector,
        ];
    }

    /**
     * @param mixed[] $array
     *
     * @phpstan-return ($array is VectorDocumentArray ? true : false)
     */
    public static function isVectorDocumentArray(array $array): bool
    {
        return count($array) === 5
            && array_key_exists('id', $array)
            && array_key_exists('documentId', $array)
            && array_key_exists('content', $array)
            && array_key_exists('vectorContentHash', $array)
            && array_key_exists('vector', $array);
    }
}
