<?php

declare(strict_types=1);

namespace ChronicleKeeper\Library\Infrastructure\VectorStorage;

use ChronicleKeeper\Library\Domain\Entity\Image;
use Symfony\Component\Uid\Uuid;

use function array_key_exists;
use function count;

/**
 * @phpstan-type VectorImageArray = array{
 *     id: string,
 *     imageId: string,
 *     content: string,
 *     vectorContentHash: string,
 *     vector: list<float>
 * }
 */
class VectorImage
{
    public string $id;

    /** @param list<float> $vector */
    public function __construct(
        public Image $image,
        public string $content,
        public string $vectorContentHash,
        public array $vector,
    ) {
        $this->id = Uuid::v4()->toString();
    }

    /** @return VectorImageArray */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'imageId' => $this->image->id,
            'content' => $this->content,
            'vectorContentHash' => $this->vectorContentHash,
            'vector' => $this->vector,
        ];
    }

    /**
     * @param mixed[] $array
     *
     * @phpstan-return ($array is VectorImageArray ? true : false)
     */
    public static function isVectorImageArray(array $array): bool
    {
        return count($array) === 5
            && array_key_exists('id', $array)
            && array_key_exists('imageId', $array)
            && array_key_exists('content', $array)
            && array_key_exists('vectorContentHash', $array)
            && array_key_exists('vector', $array);
    }
}
