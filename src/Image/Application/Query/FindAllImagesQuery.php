<?php

declare(strict_types=1);

namespace ChronicleKeeper\Image\Application\Query;

use ChronicleKeeper\Image\Domain\Entity\Image;
use ChronicleKeeper\Shared\Application\Query\Query;
use ChronicleKeeper\Shared\Application\Query\QueryParameters;
use ChronicleKeeper\Shared\Infrastructure\Database\DatabasePlatform;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

use function array_map;
use function assert;

class FindAllImagesQuery implements Query
{
    public function __construct(
        private readonly DatabasePlatform $databasePlatform,
        private readonly DenormalizerInterface $denormalizer,
    ) {
    }

    /** @return list<Image> */
    public function query(QueryParameters $parameters): array
    {
        assert($parameters instanceof FindAllImages);

        $images = $this->databasePlatform->fetch('SELECT * FROM images ORDER BY title');

        return array_map(
            fn (array $image) => $this->denormalizer->denormalize($image, Image::class),
            $images,
        );
    }
}
