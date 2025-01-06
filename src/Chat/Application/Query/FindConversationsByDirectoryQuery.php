<?php

declare(strict_types=1);

namespace ChronicleKeeper\Chat\Application\Query;

use ChronicleKeeper\Chat\Domain\Entity\Conversation;
use ChronicleKeeper\Shared\Application\Query\Query;
use ChronicleKeeper\Shared\Application\Query\QueryParameters;
use ChronicleKeeper\Shared\Infrastructure\Database\Converter\DatabaseRowConverter;
use ChronicleKeeper\Shared\Infrastructure\Database\DatabasePlatform;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

use function assert;

class FindConversationsByDirectoryQuery implements Query
{
    public function __construct(
        private readonly DenormalizerInterface $denormalizer,
        private readonly DatabasePlatform $databasePlatform,
        private readonly DatabaseRowConverter $databaseRowConverter,
    ) {
    }

    /** @return Conversation[] */
    public function query(QueryParameters $parameters): array
    {
        assert($parameters instanceof FindConversationsByDirectoryParameters);

        $data = $this->databasePlatform->fetch(
            'SELECT * FROM conversations WHERE directory = :directory ORDER BY title',
            ['directory' => $parameters->directory->getId()],
        );

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
