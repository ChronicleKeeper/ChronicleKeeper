<?php

declare(strict_types=1);

namespace ChronicleKeeper\Library\Infrastructure\Repository;

use ChronicleKeeper\Library\Domain\Entity\Directory;
use ChronicleKeeper\Library\Domain\Entity\Document;
use ChronicleKeeper\Library\Domain\Event\DocumentDeleted;
use ChronicleKeeper\Shared\Infrastructure\Persistence\Filesystem\Contracts\FileAccess;
use ChronicleKeeper\Shared\Infrastructure\Persistence\Filesystem\PathRegistry;
use DateTimeImmutable;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Log\LoggerInterface;
use RuntimeException;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Serializer\SerializerInterface;

use function array_filter;
use function json_encode;
use function strcasecmp;
use function usort;

use const JSON_PRETTY_PRINT;
use const JSON_THROW_ON_ERROR;

#[Autoconfigure(lazy: true)]
class FilesystemDocumentRepository
{
    private const string STORAGE_NAME = 'library.documents';

    public function __construct(
        private readonly LoggerInterface $logger,
        private readonly FileAccess $fileAccess,
        private readonly SerializerInterface $serializer,
        private readonly FilesystemVectorDocumentRepository $vectorRepository,
        private readonly PathRegistry $pathRegistry,
        private readonly EventDispatcherInterface $eventDispatcher,
    ) {
    }

    public function store(Document $document): void
    {
        $document->updatedAt = new DateTimeImmutable();
        $filename            = $this->generateFilename($document->id);
        $content             = json_encode($document->toArray(), JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT);

        $this->fileAccess->write(self::STORAGE_NAME, $filename, $content);
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

        return array_filter($documents, static fn (Document $document) => $document->directory->id === $directory->id);
    }

    public function findById(string $id): Document
    {
        $filename = $this->generateFilename($id);
        $json     = $this->fileAccess->read(self::STORAGE_NAME, $filename);

        return $this->serializer->deserialize($json, Document::class, 'json');
    }

    public function remove(Document $document): void
    {
        $filename = $this->generateFilename($document->id);

        foreach ($this->vectorRepository->findAllByDocumentId($document->id) as $vectors) {
            $this->vectorRepository->remove($vectors);
        }

        $this->fileAccess->delete(self::STORAGE_NAME, $filename);

        $this->eventDispatcher->dispatch(new DocumentDeleted($document->id));
    }

    /** @return non-empty-string */
    private function generateFilename(string $id): string
    {
        return $id . '.json';
    }
}
