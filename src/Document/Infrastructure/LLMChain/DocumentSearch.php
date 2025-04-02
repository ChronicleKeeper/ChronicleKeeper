<?php

declare(strict_types=1);

namespace ChronicleKeeper\Document\Infrastructure\LLMChain;

use ChronicleKeeper\Chat\Domain\ValueObject\FunctionDebug;
use ChronicleKeeper\Chat\Domain\ValueObject\Reference;
use ChronicleKeeper\Chat\Infrastructure\LLMChain\RuntimeCollector;
use ChronicleKeeper\Document\Application\Query\SearchSimilarVectors;
use ChronicleKeeper\Document\Domain\Entity\Document;
use ChronicleKeeper\Settings\Application\SettingsHandler;
use ChronicleKeeper\Shared\Application\Query\QueryService;
use ChronicleKeeper\Shared\Infrastructure\LLMChain\EmbeddingCalculator;
use PhpLlm\LlmChain\Chain\Toolbox\Attribute\AsTool;

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
class DocumentSearch
{
    private float|null $maxDistance = null;

    public function __construct(
        private readonly EmbeddingCalculator $embeddingCalculator,
        private readonly SettingsHandler $settingsHandler,
        private readonly QueryService $queryService,
        private readonly RuntimeCollector $runtimeCollector,
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

        /** @var list<array{document: Document, content: string, distance: float}> $documents */
        $documents = $this->queryService->query(new SearchSimilarVectors(
            $this->embeddingCalculator->getSingleEmbedding($search),
            $maxDistance,
            $maxResults,
        ));

        if (count($documents) === 0) {
            $this->runtimeCollector->addFunctionDebug(
                new FunctionDebug(
                    'library_documents',
                    ['search' => $search, 'maxDistance' => $maxDistance, 'maxResults' => $maxResults],
                    [],
                ),
            );

            return 'There are no matching documents.';
        }

        $debugResponse = [];

        $result = 'I have found the following information that are associated to the question:' . PHP_EOL;
        foreach ($documents as $document) {
            $libraryDocument        = $document['document'];
            $documentHierarchyTitle = $libraryDocument->getDirectory()->flattenHierarchyTitle();

            $result .= '# Title: ' . $libraryDocument->getTitle() . PHP_EOL;
            $result .= 'Storage Directory: ' . $documentHierarchyTitle . PHP_EOL;
            $result .= $document['content'] . PHP_EOL . PHP_EOL;

            $this->runtimeCollector->addReference(Reference::forDocument($libraryDocument));

            $debugResponse[] = [
                'document' => $documentHierarchyTitle . '/' . $libraryDocument->getTitle(),
                'distance' => $document['distance'],
                'content' => $document['content'],
            ];
        }

        $this->runtimeCollector->addFunctionDebug(
            new FunctionDebug(
                'library_documents',
                ['search' => $search, 'maxDistance' => $maxDistance, 'maxResults' => $maxResults],
                $debugResponse,
            ),
        );

        return $result;
    }
}
