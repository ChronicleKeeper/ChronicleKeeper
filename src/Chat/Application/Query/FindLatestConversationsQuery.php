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

class FindLatestConversationsQuery implements Query
{
    public function __construct(
        private readonly DenormalizerInterface $denormalizer,
        private readonly Connection $dbal,
        private readonly DatabaseRowConverter $databaseRowConverter,
    ) {
    }

    /** @return list<Conversation> */
    public function query(QueryParameters $parameters): array
    {
        assert($parameters instanceof FindLatestConversationsParameters);

        $data = $this->dbal->createQueryBuilder()
            ->select('*')
            ->from('conversations')
            ->orderBy('title')
            ->setMaxResults($parameters->maxEntries)
            ->executeQuery()
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
