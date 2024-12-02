<?php

declare(strict_types=1);

namespace ChronicleKeeper\Library\Infrastructure\Repository;

use ChronicleKeeper\Library\Domain\Entity\Directory;
use ChronicleKeeper\Library\Domain\Entity\Document;
use ChronicleKeeper\Shared\Infrastructure\Persistence\Filesystem\PathRegistry;
use Psr\Log\LoggerInterface;
use RuntimeException;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Serializer\SerializerInterface;

use function array_filter;
use function array_values;
use function strcasecmp;
use function usort;

#[Autoconfigure(lazy: true)]
class FilesystemDocumentRepository
{
    private const string STORAGE_NAME = 'library.documents';

    public function __construct(
        private readonly LoggerInterface $logger,
        private readonly SerializerInterface $serializer,
        private readonly PathRegistry $pathRegistry,
    ) {
    }

    /** @return list<Document> */
    public function findAll(): array
    {
        $finder = (new Finder())
            ->ignoreDotFiles(true)
            ->in($this->pathRegistry->get(self::STORAGE_NAME))
            ->files();

        $documents = [];
        foreach ($finder as $file) {
            try {
                $documents[] = $this->serializer->deserialize($file->getContents(), Document::class, 'json');
            } catch (RuntimeException $e) {
                $this->logger->error($e, ['file' => $file]);
            }
        }

        usort(
            $documents,
            static fn (Document $left, Document $right) => strcasecmp($left->title, $right->title),
        );

        return $documents;
    }

    /** @return list<Document> */
    public function findByDirectory(Directory $directory): array
    {
        $documents = $this->findAll();

        return array_values(array_filter(
            $documents,
            static fn (Document $document) => $document->directory->id === $directory->id,
        ));
    }
}
