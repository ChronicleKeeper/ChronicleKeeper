<?php

declare(strict_types=1);

namespace ChronicleKeeper\Library\Infrastructure\VectorStorage;

use ChronicleKeeper\Library\Domain\Entity\Document;
use Symfony\Component\Uid\Uuid;

use function array_key_exists;
use function count;

/**
 * @phpstan-type VectorDocumentArray = array{
 *     id: string,
 *     documentId: string,
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
        return count($array) === 4
            && array_key_exists('id', $array)
            && array_key_exists('documentId', $array)
            && array_key_exists('vectorContentHash', $array)
            && array_key_exists('vector', $array);
    }
}
