<?php

declare(strict_types=1);

namespace ChronicleKeeper\Chat\Application\Query;

use ChronicleKeeper\Chat\Domain\Entity\Conversation;
use ChronicleKeeper\Shared\Application\Query\Query;
use ChronicleKeeper\Shared\Application\Query\QueryParameters;
use ChronicleKeeper\Shared\Infrastructure\Persistence\Filesystem\Contracts\FileAccess;
use ChronicleKeeper\Shared\Infrastructure\Persistence\Filesystem\PathRegistry;
use ChronicleKeeper\Shared\Infrastructure\Persistence\Filesystem\SymfonyFinder;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\SerializerInterface;

use function assert;
use function count;

class FindLatestConversationsQuery implements Query
{
    public function __construct(
        private readonly PathRegistry $pathRegistry,
        private readonly FileAccess $fileAccess,
        private readonly SerializerInterface $serializer,
        private readonly SymfonyFinder $finder,
    ) {
    }

    /** @return list<Conversation> */
    public function query(QueryParameters $parameters): array
    {
        assert($parameters instanceof FindLatestConversationsParameters);

        $files = $this->finder->findFilesInDirectoryOrderedByAccessTimestamp(
            $this->pathRegistry->get('library.conversations'),
        );

        $conversations = [];
        foreach ($files as $file) {
            $filename = $file->getFilename();
            assert($filename !== '');

            $content = $this->fileAccess->read('library.conversations', $filename);
            assert($content !== '');

            $conversations[] = $this->serializer->deserialize(
                $content,
                Conversation::class,
                JsonEncoder::FORMAT,
            );

            if (count($conversations) === $parameters->maxEntries) {
                break;
            }
        }

        return $conversations;
    }
}
