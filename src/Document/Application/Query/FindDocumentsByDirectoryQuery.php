<?php

declare(strict_types=1);

namespace ChronicleKeeper\Document\Application\Query;

use ChronicleKeeper\Document\Domain\Entity\Document;
use ChronicleKeeper\Shared\Application\Query\Query;
use ChronicleKeeper\Shared\Application\Query\QueryParameters;
use ChronicleKeeper\Shared\Infrastructure\Persistence\Filesystem\Contracts\FileAccess;
use ChronicleKeeper\Shared\Infrastructure\Persistence\Filesystem\Contracts\Finder;
use ChronicleKeeper\Shared\Infrastructure\Persistence\Filesystem\PathRegistry;
use Psr\Log\LoggerInterface;
use RuntimeException;
use Symfony\Component\Serializer\SerializerInterface;

use function array_filter;
use function array_values;
use function assert;
use function strcasecmp;
use function usort;

class FindDocumentsByDirectoryQuery implements Query
{
    public function __construct(
        private readonly PathRegistry $pathRegistry,
        private readonly Finder $finder,
        private readonly FileAccess $fileAccess,
        private readonly SerializerInterface $serializer,
        private readonly LoggerInterface $logger,
    ) {
    }

    /** @return list<Document> */
    public function query(QueryParameters $parameters): array
    {
        assert($parameters instanceof FindDocumentsByDirectory);

        $index = $this->fileAccess->readIndex('library.documents');
        $documents = [];

        foreach ($index as $file => $metadata) {
            if ($metadata['directory_id'] === $parameters->id) {
                try {
                    $documents[] = $this->deserialize($file);
                } catch (RuntimeException $e) {
                    $this->logger->error($e, ['file' => $file]);
                }
            }
        }

        usort(
            $documents,
            static fn (Document $left, Document $right) => strcasecmp($left->title, $right->title),
        );

        return $documents;
    }

    private function deserialize(string $file): Document
    {
        assert(!empty($file), 'The given file must not be empty.');

        return $this->serializer->deserialize(
            $this->fileAccess->read('library.documents', $file),
            Document::class,
            'json',
        );
    }
}
