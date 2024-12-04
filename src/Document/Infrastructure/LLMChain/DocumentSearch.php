<?php

declare(strict_types=1);

namespace ChronicleKeeper\Document\Infrastructure\LLMChain;

use ChronicleKeeper\Document\Application\Query\SearchSimilarVectors;
use ChronicleKeeper\Document\Domain\Entity\Document;
use ChronicleKeeper\Document\Domain\Entity\VectorDocument;
use ChronicleKeeper\Settings\Application\SettingsHandler;
use ChronicleKeeper\Shared\Application\Query\QueryService;
use ChronicleKeeper\Shared\Infrastructure\LLMChain\EmbeddingCalculator;
use ChronicleKeeper\Shared\Infrastructure\LLMChain\ToolUsageCollector;
use PhpLlm\LlmChain\Chain\ToolBox\Attribute\AsTool;

use function array_values;
use function count;

use const PHP_EOL;

#[AsTool(
    'library_documents',
    <<<'TEXT'
    Provides textual background information related to the role-playing game world. This includes details on places,
    characters, religions, and other elements existing within the game universe. For visual representations,
    consider using the "library_images" function.
    TEXT,
)]
final class DocumentSearch
{
    /** @var array<string, Document> */
    private array $referencedDocuments = [];

    private float|null $maxDistance = null;

    public function __construct(
        private readonly EmbeddingCalculator $embeddingCalculator,
        private readonly SettingsHandler $settingsHandler,
        private readonly ToolUsageCollector $collector,
        private readonly QueryService $queryService,
    ) {
    }

    public function setOneTimeMaxDistance(float|null $maxDistance): void
    {
        $this->maxDistance = $maxDistance;
    }

    /** @param string $search The search parameter containing the user's question or relevant keywords related to the information they seek */
    public function __invoke(string $search): string
    {
        $settings    = $this->settingsHandler->get();
        $maxResults  = $settings->getChatbotGeneral()->getMaxDocumentResponses();
        $maxDistance = $this->maxDistance ?? $settings->getChatbotTuning()->getDocumentsMaxDistance();

        /** @var list<array{vector: VectorDocument, distance: float}> $documents */
        $documents = $this->queryService->query(new SearchSimilarVectors(
            $this->embeddingCalculator->getSingleEmbedding($search),
            $maxDistance,
            $maxResults,
        ));

        $this->referencedDocuments = [];
        if (count($documents) === 0) {
            $this->collector->called(
                'library_documents',
                [
                    'arguments' => ['search' => $search, 'maxDistance' => $maxDistance, 'maxResults' => $maxResults],
                    'responses' => [],
                ],
            );

            return 'There are no matching documents.';
        }

        $debugResponse = [];

        $result = 'I have found the following information that are associated to the question:' . PHP_EOL;
        foreach ($documents as $document) {
            $libraryDocument = $document['vector']->document;

            $result .= '# Title: ' . $libraryDocument->title . PHP_EOL;
            $result .= 'Storage Directory: ' . $libraryDocument->directory->flattenHierarchyTitle() . PHP_EOL;
            $result .= $document['vector']->content . PHP_EOL . PHP_EOL;

            $this->referencedDocuments[$libraryDocument->id] = $libraryDocument;

            $debugResponse[] = [
                'document' => $libraryDocument->directory->flattenHierarchyTitle()
                    . '/' . $libraryDocument->title,
                'distance' => $document['distance'],
                'content' => $document['vector']->content,
            ];
        }

        $this->collector->called(
            'library_documents',
            [
                'arguments' => ['search' => $search, 'maxDistance' => $this->maxDistance, 'maxResults' => $maxResults],
                'responses' => $debugResponse,
            ],
        );

        return $result;
    }

    /** @return list<Document> */
    public function getReferencedDocuments(): array
    {
        $documents                 = array_values($this->referencedDocuments);
        $this->referencedDocuments = [];

        return $documents;
    }
}
