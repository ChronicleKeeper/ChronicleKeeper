<?php

declare(strict_types=1);

namespace DZunke\NovDoc\Infrastructure\Repository;

use DZunke\NovDoc\Domain\VectorStorage\Distance\CosineDistance;
use DZunke\NovDoc\Domain\VectorStorage\VectorDocument;
use Psr\Log\LoggerInterface;
use RuntimeException;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;

use function array_filter;
use function array_keys;
use function array_slice;
use function asort;
use function file_exists;
use function file_get_contents;
use function file_put_contents;
use function is_array;
use function is_readable;
use function json_decode;
use function json_encode;
use function json_validate;

use const DIRECTORY_SEPARATOR;
use const JSON_PRETTY_PRINT;

class FilesystemVectorDocumentRepository
{
    public function __construct(
        private readonly LoggerInterface $logger,
        private readonly FilesystemDocumentRepository $documentRepository,
        private readonly CosineDistance $distance,
        private readonly Filesystem $filesystem,
        private readonly string $filesystemEmbeddingStoragePath,
    ) {
    }

    public function store(VectorDocument $vectorDocument): void
    {
        $filename        = $vectorDocument->id . '.json';
        $filepath        = $this->filesystemEmbeddingStoragePath . DIRECTORY_SEPARATOR . $filename;
        $documentAsArray = $vectorDocument->toArray();
        $documentAsJson  = json_encode($documentAsArray, JSON_PRETTY_PRINT);

        file_put_contents($filepath, $documentAsJson);
    }

    /** @return list<VectorDocument> */
    public function findAll(): array
    {
        $documentFinder = (new Finder())
            ->ignoreDotFiles(true)
            ->in($this->filesystemEmbeddingStoragePath)
            ->files();

        $documents = [];
        foreach ($documentFinder as $documentFound) {
            try {
                $documents[] = $this->convertJsonToVectorDocument($documentFound->getContents());
            } catch (RuntimeException $e) {
                $this->logger->debug($e);
            }
        }

        return $documents;
    }

    public function findById(string $id): VectorDocument|null
    {
        $documentJson = $this->getContentOfVectorDocumentFile($id . '.json');
        if ($documentJson === null || ! json_validate($documentJson)) {
            return null;
        }

        try {
            return $this->convertJsonToVectorDocument($documentJson);
        } catch (RuntimeException) {
            return null;
        }
    }

    /**
     * @param list<float> $searchedVectors
     *
     * @return list<VectorDocument>
     */
    public function findSimilarDocuments(array $searchedVectors, int $maxResults = 4): array
    {
        $distances       = [];
        $vectorDocuments = $this->findAll();

        foreach ($vectorDocuments as $index => $document) {
            $dist              = $this->distance->measure($searchedVectors, $document->vector);
            $distances[$index] = $dist;
        }

        asort($distances); // Sort by distance (ascending).

        $topKIndices = array_slice(array_keys($distances), 0, $maxResults, true);

        $results = [];
        foreach ($topKIndices as $index) {
            $results[] = $vectorDocuments[$index];
        }

        return $results;
    }

    /** @return list<VectorDocument> */
    public function findAllByDocumentId(string $id): array
    {
        return array_filter(
            $this->findAll(),
            static fn (VectorDocument $vectorDocument): bool => $vectorDocument->document->id === $id,
        );
    }

    public function remove(VectorDocument $vectorDocument): void
    {
        $filepath = $this->filesystemEmbeddingStoragePath . DIRECTORY_SEPARATOR . $vectorDocument->id . '.json';
        if (! file_exists($filepath) || ! is_readable($filepath)) {
            return;
        }

        $this->filesystem->remove($filepath);
    }

    private function convertJsonToVectorDocument(string $json): VectorDocument
    {
        $vectorDocumentArr = json_decode($json, true);

        if (! is_array($vectorDocumentArr) || ! VectorDocument::isVectorDocumentArray($vectorDocumentArr)) {
            throw new RuntimeException('Document to load contain invalid content.');
        }

        $document = $this->documentRepository->findById($vectorDocumentArr['documentId']);
        if ($document === null) {
            throw new RuntimeException('The vector document "' . $vectorDocumentArr['id'] . '" have an invalid link to a document.');
        }

        $vectorDocument     = new VectorDocument(
            document: $document,
            vectorContentHash: $vectorDocumentArr['vectorContentHash'],
            vector: $vectorDocumentArr['vector'],
        );
        $vectorDocument->id = $vectorDocumentArr['id'];

        return $vectorDocument;
    }

    private function getContentOfVectorDocumentFile(string $filename): string|null
    {
        $filepath = $this->filesystemEmbeddingStoragePath . DIRECTORY_SEPARATOR . $filename;
        if (! file_exists($filepath) || is_readable($filepath)) {
            return null;
        }

        $vectorDocumentJson = file_get_contents($filepath);
        if ($vectorDocumentJson === false || ! json_validate($vectorDocumentJson)) {
            return null;
        }

        return $vectorDocumentJson;
    }
}
