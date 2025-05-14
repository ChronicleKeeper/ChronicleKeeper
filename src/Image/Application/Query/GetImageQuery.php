<?php

declare(strict_types=1);

namespace ChronicleKeeper\Image\Application\Query;

use ChronicleKeeper\Image\Domain\Entity\Image;
use ChronicleKeeper\Shared\Application\Query\Query;
use ChronicleKeeper\Shared\Application\Query\QueryParameters;
use Doctrine\DBAL\Connection;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

use function assert;

class GetImageQuery implements Query
{
    public function __construct(
        private readonly Connection $connection,
        private readonly DenormalizerInterface $denormalizer,
    ) {
    }

    public function query(QueryParameters $parameters): Image
    {
        assert($parameters instanceof GetImage);

        $image = $this->connection->createQueryBuilder()
            ->select('*')
            ->from('images')
            ->where('id = :id')
            ->setParameter('id', $parameters->id)
            ->executeQuery()
            ->fetchAssociative();

        return $this->denormalizer->denormalize($image, Image::class);
    }
}
