<?php

declare(strict_types=1);

namespace ChronicleKeeper\Image\Application\Query;

use ChronicleKeeper\Image\Domain\Entity\Image;
use ChronicleKeeper\Shared\Application\Query\Query;
use ChronicleKeeper\Shared\Application\Query\QueryParameters;
use ChronicleKeeper\Shared\Infrastructure\Persistence\Filesystem\Contracts\FileAccess;
use Symfony\Component\Serializer\SerializerInterface;

use function assert;

class GetImageQuery implements Query
{
    public function __construct(
        private readonly FileAccess $fileAccess,
        private readonly SerializerInterface $serializer,
    ) {
    }

    public function query(QueryParameters $parameters): Image
    {
        assert($parameters instanceof GetImage);

        return $this->serializer->deserialize(
            $this->fileAccess->read('library.images', $parameters->id . '.json'),
            Image::class,
            'json',
        );
    }
}
