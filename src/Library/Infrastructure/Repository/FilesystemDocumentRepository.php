<?php

declare(strict_types=1);

namespace ChronicleKeeper\Library\Infrastructure\Repository;

use ChronicleKeeper\Library\Domain\Entity\Directory;
use ChronicleKeeper\Library\Domain\Entity\Document;
use DateTimeImmutable;
use Psr\Log\LoggerInterface;
use RuntimeException;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Serializer\SerializerInterface;

use function array_filter;
use function file_exists;
use function file_get_contents;
use function file_put_contents;
use function is_readable;
use function json_encode;
use function json_validate;
use function strcasecmp;
use function usort;

use const DIRECTORY_SEPARATOR;
use const JSON_PRETTY_PRINT;

#[Autoconfigure(lazy: true)]
class FilesystemDocumentRepository
{
    public function __construct(
        private readonly LoggerInterface $logger,
        private readonly Filesystem $filesystem,
        private readonly string $documentStoragePath,
        private readonly SerializerInterface $serializer,
        private readonly FilesystemVectorDocumentRepository $vectorRepository,
    ) {
    }

    public function store(Document $document): void
    {
        // When stored it is updated! Maybe change later with a change detection ... but yeah .. it is changed for now
        $document->updatedAt = new DateTimeImmutable();

        $filename        = $document->id . '.json';
        $filepath        = $this->documentStoragePath . DIRECTORY_SEPARATOR . $filename;
        $documentAsArray = $document->toArray();
        $documentAsJson  = json_encode($documentAsArray, JSON_PRETTY_PRINT);

        file_put_contents($filepath, $documentAsJson);
    }

    /** @return list<Document> */
    public function findAll(): array
    {
        $documentFinder = (new Finder())
            ->ignoreDotFiles(true)
            ->in($this->documentStoragePath)
            ->files();

        $documents = [];
        foreach ($documentFinder as $documentFound) {
            try {
                $documents[] = $this->serializer->deserialize($documentFound->getContents(), Document::class, 'json');
            } catch (RuntimeException $e) {
                $this->logger->error($e, ['file' => $documentFound]);
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

        return array_filter($documents, static function (Document $document) use ($directory) {
            return $document->directory->id === $directory->id;
        });
    }

    public function findById(string $id): Document|null
    {
        $documentJson = $this->getContentOfDocumentFile($id . '.json');

        if ($documentJson === null) {
            return null;
        }

        return $this->serializer->deserialize($documentJson, Document::class, 'json');
    }

    public function remove(Document $document): void
    {
        $filepath = $this->documentStoragePath . DIRECTORY_SEPARATOR . $document->id . '.json';
        if (! file_exists($filepath) || ! is_readable($filepath)) {
            return;
        }

        foreach ($this->vectorRepository->findAllByDocumentId($document->id) as $vectors) {
            $this->vectorRepository->remove($vectors);
        }

        $this->filesystem->remove($filepath);
    }

    private function getContentOfDocumentFile(string $filename): string|null
    {
        $filepath = $this->documentStoragePath . DIRECTORY_SEPARATOR . $filename;
        if (! file_exists($filepath) || ! is_readable($filepath)) {
            return null;
        }

        $documentJson = file_get_contents($filepath);
        if ($documentJson === false || ! json_validate($documentJson)) {
            return null;
        }

        return $documentJson;
    }
}
