<?php

declare(strict_types=1);

namespace ChronicleKeeper\Image\Application\Query;

use ChronicleKeeper\Library\Infrastructure\VectorStorage\VectorImage;
use ChronicleKeeper\Shared\Application\Query\Query;
use ChronicleKeeper\Shared\Application\Query\QueryParameters;
use ChronicleKeeper\Shared\Infrastructure\Persistence\Filesystem\Contracts\FileAccess;
use Symfony\Component\Serializer\SerializerInterface;

use function assert;

class GetVectorImageQuery implements Query
{
    public function __construct(
        private readonly FileAccess $fileAccess,
        private readonly SerializerInterface $serializer,
    ) {
    }

    public function query(QueryParameters $parameters): VectorImage
    {
        assert($parameters instanceof GetVectorImage);

        return $this->serializer->deserialize(
            $this->fileAccess->read('vector.images', $parameters->id . '.json'),
            VectorImage::class,
            'json',
        );
    }
}
