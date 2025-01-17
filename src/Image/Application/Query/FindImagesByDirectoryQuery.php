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

class FindImagesByDirectoryQuery implements Query
{
    public function __construct(
        private readonly DatabasePlatform $databasePlatform,
        private readonly DenormalizerInterface $denormalizer,
    ) {
    }

    /** @return list<Image> */
    public function query(QueryParameters $parameters): array
    {
        assert($parameters instanceof FindImagesByDirectory);

        $images = $this->databasePlatform->fetch(
            'SELECT * FROM images WHERE directory = :directory_id ORDER BY title',
            ['directory_id' => $parameters->id],
        );

        return array_map(
            fn ($image) => $this->denormalizer->denormalize($image, Image::class),
            $images,
        );
    }
}
