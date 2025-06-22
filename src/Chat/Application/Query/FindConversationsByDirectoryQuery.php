<?php

declare(strict_types=1);

namespace ChronicleKeeper\Chat\Application\Query;

use ChronicleKeeper\Chat\Domain\Entity\Conversation;
use ChronicleKeeper\Shared\Application\Query\Query;
use ChronicleKeeper\Shared\Application\Query\QueryParameters;
use ChronicleKeeper\Shared\Infrastructure\Database\Converter\DatabaseRowConverter;
use Doctrine\DBAL\Connection;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

use function assert;

class FindConversationsByDirectoryQuery implements Query
{
    public function __construct(
        private readonly DenormalizerInterface $denormalizer,
        private readonly DatabaseRowConverter $databaseRowConverter,
        private readonly Connection $connection,
    ) {
    }

    /** @return Conversation[] */
    public function query(QueryParameters $parameters): array
    {
        assert($parameters instanceof FindConversationsByDirectoryParameters);

        $data = $this->connection->createQueryBuilder()
            ->select('*')
            ->from('conversations')
            ->where('directory = :directory')
            ->setParameter('directory', $parameters->directory->getId())
            ->orderBy('title')
            ->fetchAllAssociative();

        $conversations = [];
        foreach ($data as $conversation) {
            $conversations[] = $this->denormalizer->denormalize(
                $this->databaseRowConverter->convert($conversation, Conversation::class),
                Conversation::class,
            );
        }

        return $conversations;
    }
}
