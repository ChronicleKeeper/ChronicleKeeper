<?php

declare(strict_types=1);

namespace ChronicleKeeper\Library\Application\Query;

use ChronicleKeeper\Library\Domain\Entity\Directory;
use ChronicleKeeper\Library\Domain\RootDirectory;
use ChronicleKeeper\Shared\Application\Query\Query;
use ChronicleKeeper\Shared\Application\Query\QueryParameters;
use ChronicleKeeper\Shared\Infrastructure\Database\DatabasePlatform;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

use function assert;

final class FindDirectoryByIdQuery implements Query
{
    public function __construct(
        private readonly DatabasePlatform $databasePlatform,
        private readonly DenormalizerInterface $denormalizer,
    ) {
    }

    public function query(QueryParameters $parameters): Directory
    {
        assert($parameters instanceof FindDirectoryById);

        if ($parameters->id === RootDirectory::ID) {
            return RootDirectory::get();
        }

        $directory = $this->databasePlatform->fetchSingleRow(
            'SELECT * FROM directories WHERE id = :id',
            ['id' => $parameters->id],
        );

        return $this->denormalizer->denormalize($directory, Directory::class);
    }
}
