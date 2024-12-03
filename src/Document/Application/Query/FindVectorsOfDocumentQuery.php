<?php

declare(strict_types=1);

namespace ChronicleKeeper\Document\Application\Query;

use ChronicleKeeper\Library\Infrastructure\VectorStorage\VectorDocument;
use ChronicleKeeper\Shared\Application\Query\Query;
use ChronicleKeeper\Shared\Application\Query\QueryParameters;
use ChronicleKeeper\Shared\Infrastructure\Persistence\Filesystem\Contracts\FileAccess;
use ChronicleKeeper\Shared\Infrastructure\Persistence\Filesystem\Contracts\Finder;
use ChronicleKeeper\Shared\Infrastructure\Persistence\Filesystem\Exception\UnableToReadFile;
use ChronicleKeeper\Shared\Infrastructure\Persistence\Filesystem\PathRegistry;
use Psr\Log\LoggerInterface;
use RuntimeException;
use Symfony\Component\Serializer\SerializerInterface;

use function array_filter;
use function array_values;
use function assert;

class FindVectorsOfDocumentQuery implements Query
{
    public function __construct(
        private readonly PathRegistry $pathRegistry,
        private readonly Finder $finder,
        private readonly FileAccess $fileAccess,
        private readonly SerializerInterface $serializer,
        private readonly LoggerInterface $logger,
    ) {
    }

    /** @return list<VectorDocument> */
    public function query(QueryParameters $parameters): array
    {
        assert($parameters instanceof FindVectorsOfDocument);

        $files = $this->finder->findFilesInDirectory($this->pathRegistry->get('vector.documents'));

        $documents = [];
        foreach ($files as $file) {
            try {
                $filename = $file->getFilename();
                assert($filename !== '');

                $content = $this->fileAccess->read('vector.documents', $filename);
                assert($content !== '');

                $documents[] = $this->serializer->deserialize($content, VectorDocument::class, 'json');
            } catch (RuntimeException | UnableToReadFile $e) {
                $this->logger->error($e, ['file' => $file]);
            }
        }

        return array_values(array_filter(
            $documents,
            static fn (VectorDocument $document) => $document->document->id === $parameters->id,
        ));
    }
}
