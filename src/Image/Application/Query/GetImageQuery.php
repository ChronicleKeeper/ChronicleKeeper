<?php

declare(strict_types=1);

namespace ChronicleKeeper\Image\Application\Query;

use ChronicleKeeper\Image\Domain\Entity\Image;
use ChronicleKeeper\Shared\Application\Query\Query;
use ChronicleKeeper\Shared\Application\Query\QueryParameters;
use ChronicleKeeper\Shared\Infrastructure\Database\DatabasePlatform;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

use function assert;

class GetImageQuery implements Query
{
    public function __construct(
        private readonly DatabasePlatform $databasePlatform,
        private readonly DenormalizerInterface $denormalizer,
    ) {
    }

    public function query(QueryParameters $parameters): Image
    {
        assert($parameters instanceof GetImage);

        $image = $this->databasePlatform->fetchSingleRow(
            'SELECT * FROM images WHERE id = :id',
            ['id' => $parameters->id],
        );

        return $this->denormalizer->denormalize($image, Image::class);
    }
}
