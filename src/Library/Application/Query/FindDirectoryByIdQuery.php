<?php

declare(strict_types=1);

namespace ChronicleKeeper\Library\Application\Query;

use ChronicleKeeper\Library\Domain\Entity\Directory;
use ChronicleKeeper\Library\Domain\RootDirectory;
use ChronicleKeeper\Shared\Application\Query\Query;
use ChronicleKeeper\Shared\Application\Query\QueryParameters;
use Doctrine\DBAL\Connection;
use RuntimeException;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

use function assert;

final class FindDirectoryByIdQuery implements Query
{
    public function __construct(
        private readonly Connection $connection,
        private readonly DenormalizerInterface $denormalizer,
    ) {
    }

    public function query(QueryParameters $parameters): Directory
    {
        assert($parameters instanceof FindDirectoryById);

        if ($parameters->id === RootDirectory::ID) {
            return RootDirectory::get();
        }

        $result = $this->connection->createQueryBuilder()
            ->select('*')
            ->from('directories')
            ->where('id = :id')
            ->setParameter('id', $parameters->id)
            ->executeQuery()
            ->fetchAssociative();

        if ($result === false) {
            throw new RuntimeException('Directory not found with id: ' . $parameters->id);
        }

        return $this->denormalizer->denormalize($result, Directory::class);
    }
}
