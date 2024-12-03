<?php

declare(strict_types=1);

namespace ChronicleKeeper\Library\Infrastructure\Repository;

use ChronicleKeeper\Document\Application\Query\GetDocument;
use ChronicleKeeper\Library\Infrastructure\VectorStorage\Distance\CosineDistance;
use ChronicleKeeper\Library\Infrastructure\VectorStorage\VectorDocument;
use ChronicleKeeper\Settings\Application\SettingsHandler;
use ChronicleKeeper\Shared\Application\Query\QueryService;
use ChronicleKeeper\Shared\Infrastructure\Persistence\Filesystem\Exception\UnableToReadFile;
use ChronicleKeeper\Shared\Infrastructure\Persistence\Filesystem\PathRegistry;
use Psr\Log\LoggerInterface;
use RuntimeException;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;
use Symfony\Component\Finder\Finder;

use function array_keys;
use function array_slice;
use function asort;
use function is_array;
use function json_decode;

#[Autoconfigure(lazy: true)]
class FilesystemVectorDocumentRepository
{
    private const string STORAGE_NAME = 'vector.documents';

    public function __construct(
        private readonly LoggerInterface $logger,
        private readonly CosineDistance $distance,
        private readonly SettingsHandler $settingsHandler,
        private readonly PathRegistry $pathRegistry,
        private readonly QueryService $queryService,
    ) {
    }

    /** @return list<VectorDocument> */
    public function findAll(): array
    {
        $finder = (new Finder())
            ->ignoreDotFiles(true)
            ->in($this->pathRegistry->get(self::STORAGE_NAME))
            ->files();

        $documents = [];
        foreach ($finder as $file) {
            try {
                $documents[] = $this->convertJsonToVectorDocument($file->getContents());
            } catch (RuntimeException | UnableToReadFile $e) {
                $this->logger->debug($e);
            }
        }

        return $documents;
    }

    /**
     * @param list<float> $searchedVectors
     *
     * @return list<array{vector: VectorDocument, distance: float}>
     */
    public function findSimilar(array $searchedVectors, float|null $maxDistance = null, int $maxResults = 4): array
    {
        $distances       = [];
        $vectorDocuments = $this->findAll();

        $maxDistance ??= $this->settingsHandler->get()->getChatbotTuning()->getDocumentsMaxDistance();

        foreach ($vectorDocuments as $index => $document) {
            if ($document->document->content === '') {
                continue;
            }

            $dist = $this->distance->measure($searchedVectors, $document->vector);
            if ($dist > $maxDistance) {
                unset($vectorDocuments[$index]);
                continue;
            }

            $distances[$index] = $dist;
        }

        asort($distances);

        $topKIndices = array_slice(array_keys($distances), 0, $maxResults, true);

        $results = [];
        foreach ($topKIndices as $index) {
            $results[] = [
                'vector' => $vectorDocuments[$index],
                'distance' => $distances[$index],
            ];
        }

        return $results;
    }

    private function convertJsonToVectorDocument(string $json): VectorDocument
    {
        $vectorDocumentArr = json_decode($json, true);

        if (! is_array($vectorDocumentArr) || ! VectorDocument::isVectorDocumentArray($vectorDocumentArr)) {
            throw new RuntimeException('Document to load contains invalid content.');
        }

        $document = $this->queryService->query(new GetDocument($vectorDocumentArr['documentId']));

        $vectorDocument     = new VectorDocument(
            document: $document,
            content: $vectorDocumentArr['content'],
            vectorContentHash: $vectorDocumentArr['vectorContentHash'],
            vector: $vectorDocumentArr['vector'],
        );
        $vectorDocument->id = $vectorDocumentArr['id'];

        return $vectorDocument;
    }
}
