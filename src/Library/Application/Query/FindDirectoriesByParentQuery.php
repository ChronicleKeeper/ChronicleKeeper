<?php

declare(strict_types=1);

namespace ChronicleKeeper\Library\Application\Query;

use ChronicleKeeper\Library\Domain\Entity\Directory;
use ChronicleKeeper\Shared\Application\Query\Query;
use ChronicleKeeper\Shared\Application\Query\QueryParameters;
use ChronicleKeeper\Shared\Infrastructure\Database\DatabasePlatform;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

use function array_map;
use function assert;
use function strcasecmp;
use function usort;

final class FindDirectoriesByParentQuery implements Query
{
    public function __construct(
        private readonly DatabasePlatform $databasePlatform,
        private readonly DenormalizerInterface $denormalizer,
    ) {
    }

    /** @return list<Directory> */
    public function query(QueryParameters $parameters): array
    {
        assert($parameters instanceof FindDirectoriesByParent);

        $directories = $this->databasePlatform->createQueryBuilder()->createSelect()
            ->from('directories')
            ->where('parent', '=', $parameters->parentId)
            ->fetchAll();

        $directories = array_map(
            fn (array $directory) => $this->denormalizer->denormalize($directory, Directory::class),
            $directories,
        );

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
