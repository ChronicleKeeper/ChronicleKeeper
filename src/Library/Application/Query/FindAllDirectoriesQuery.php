<?php

declare(strict_types=1);

namespace ChronicleKeeper\Library\Application\Query;

use ChronicleKeeper\Library\Domain\Entity\Directory;
use ChronicleKeeper\Library\Domain\RootDirectory;
use ChronicleKeeper\Shared\Application\Query\Query;
use ChronicleKeeper\Shared\Application\Query\QueryParameters;
use Doctrine\DBAL\Connection;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

use function array_map;
use function assert;
use function strcasecmp;
use function usort;

final class FindAllDirectoriesQuery implements Query
{
    public function __construct(
        private readonly Connection $connection,
        private readonly DenormalizerInterface $denormalizer,
    ) {
    }

    /** @return list<Directory> */
    public function query(QueryParameters $parameters): array
    {
        assert($parameters instanceof FindAllDirectories);

        $queryBuilder = $this->connection->createQueryBuilder();
        $result       = $queryBuilder
            ->select('*')
            ->from('directories')
            ->executeQuery();

        $directories = $result->fetchAllAssociative();

        $directories = array_map(
            fn (array $directory) => $this->denormalizer->denormalize($directory, Directory::class),
            $directories,
        );

        // Always add the root directory to the list
        $directories[] = RootDirectory::get();

        usort(
            $directories,
            static fn (Directory $left, Directory $right) => strcasecmp(
                $left->flattenHierarchyTitle(),
                $right->flattenHierarchyTitle(),
            ),
        );

        return $directories;
    }
}
