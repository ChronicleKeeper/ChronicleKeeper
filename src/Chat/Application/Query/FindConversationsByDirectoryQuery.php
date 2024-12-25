<?php

declare(strict_types=1);

namespace ChronicleKeeper\Chat\Application\Query;

use ChronicleKeeper\Chat\Domain\Entity\Conversation;
use ChronicleKeeper\Shared\Application\Query\Query;
use ChronicleKeeper\Shared\Application\Query\QueryParameters;
use ChronicleKeeper\Shared\Infrastructure\Persistence\Filesystem\Contracts\FileAccess;
use ChronicleKeeper\Shared\Infrastructure\Persistence\Filesystem\Contracts\Finder;
use ChronicleKeeper\Shared\Infrastructure\Persistence\Filesystem\PathRegistry;
use Psr\Log\LoggerInterface;
use RuntimeException;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\SerializerInterface;
use Webmozart\Assert\Assert;

use function array_filter;
use function assert;
use function strcasecmp;
use function usort;

class FindConversationsByDirectoryQuery implements Query
{
    public function __construct(
        private readonly FileAccess $fileAccess,
        private readonly SerializerInterface $serializer,
        private readonly LoggerInterface $logger,
        private readonly PathRegistry $pathRegistry,
        private readonly Finder $finder,
    ) {
    }

    /** @return Conversation[] */
    public function query(QueryParameters $parameters): array
    {
        assert($parameters instanceof FindConversationsByDirectoryParameters);

        $conversations = [];
        foreach ($this->finder->findFilesInDirectory($this->pathRegistry->get('library.conversations')) as $file) {
            try {
                $conversations[] = $this->deserialize($file->getFilename());
            } catch (RuntimeException $e) {
                $this->logger->error($e, ['file' => $file]);
            }
        }

        $conversations = array_filter(
            $conversations,
            static fn (Conversation $conversation) => $conversation->getDirectory()->id === $parameters->directory->id,
        );

        usort(
            $conversations,
            static fn (Conversation $left, Conversation $right) => strcasecmp($left->getTitle(), $right->getTitle()),
        );

        return $conversations;
    }

    private function deserialize(string $file): Conversation
    {
        Assert::notEmpty($file, 'The given file must not be empty.');

        return $this->serializer->deserialize(
            $this->fileAccess->read('library.conversations', $file),
            Conversation::class,
            JsonEncoder::FORMAT,
        );
    }
}
