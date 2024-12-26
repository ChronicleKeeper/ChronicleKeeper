<?php

declare(strict_types=1);

namespace ChronicleKeeper\Library\Infrastructure\VectorStorage;

use ChronicleKeeper\Image\Domain\Entity\Image;
use ChronicleKeeper\Image\Domain\Entity\SearchVector;
use Symfony\Component\Uid\Uuid;

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

    public function toSearchVector(): SearchVector
    {
        return new SearchVector(
            $this->id,
            $this->image->getId(),
            $this->vector,
        );
    }

    /** @return VectorImageArray */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'imageId' => $this->image->getId(),
            'content' => $this->content,
            'vectorContentHash' => $this->vectorContentHash,
            'vector' => $this->vector,
        ];
    }
}
