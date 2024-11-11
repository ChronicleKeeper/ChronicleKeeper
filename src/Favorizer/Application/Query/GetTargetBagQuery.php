<?php

declare(strict_types=1);

namespace ChronicleKeeper\Favorizer\Application\Query;

use ChronicleKeeper\Favorizer\Domain\TargetBag;
use ChronicleKeeper\Favorizer\Domain\ValueObject\Target;
use ChronicleKeeper\Shared\Application\Query\Query;
use ChronicleKeeper\Shared\Application\Query\QueryParameters;
use ChronicleKeeper\Shared\Infrastructure\Persistence\Filesystem\Contracts\FileAccess;
use ChronicleKeeper\Shared\Infrastructure\Persistence\Filesystem\Exception\UnableToReadFile;
use Symfony\Component\Serializer\SerializerInterface;

final readonly class GetTargetBagQuery implements Query
{
    public function __construct(
        private FileAccess $fileAccess,
        private SerializerInterface $serializer,
    ) {
    }

    public function query(QueryParameters $parameters): TargetBag
    {
        try {
            $content = $this->fileAccess->read('storage', 'favorites.json');
        } catch (UnableToReadFile) {
            return new TargetBag();
        }

        return new TargetBag(...$this->serializer->deserialize($content, Target::class . '[]', 'json'));
    }
}
