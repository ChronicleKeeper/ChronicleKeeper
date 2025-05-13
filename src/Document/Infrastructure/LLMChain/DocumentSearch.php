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
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

use function array_filter;
use function array_map;
use function array_reduce;
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
        private readonly UrlGeneratorInterface $router,
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

        /** @var list<array{document: Document, content: string, distance: float}> $vectors */
        $vectors = $this->queryService->query(new SearchSimilarVectors(
            $this->embeddingCalculator->getSingleEmbedding($search),
            $maxDistance,
            $maxResults,
        ));

        if (count($vectors) === 0) {
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

        // Group by document
        /** @var array<string, array{document: Document, content: string, distance: float}> $documents */
        $documents = array_reduce(
            $vectors,
            static function (array $carry, array $vector) {
                $carry[$vector['document']->getId()] = $vector;

                return $carry;
            },
            [],
        );

        $result = 'I have found the following information that are associated to the question:' . PHP_EOL;
        foreach ($documents as $document) {
            $libraryDocument        = $document['document'];
            $documentHierarchyTitle = $libraryDocument->getDirectory()->flattenHierarchyTitle();

            $result .= '# Title: ' . $libraryDocument->getTitle() . PHP_EOL;
            $result .= '- Storage Directory: ' . $documentHierarchyTitle . PHP_EOL;
            $result .= '- Url: ' . $this->router->generate(
                'library_document_view',
                ['document' => $libraryDocument->getId()],
                UrlGeneratorInterface::ABSOLUTE_URL,
            ) . PHP_EOL . PHP_EOL;

            $result .= $libraryDocument->getContent() . PHP_EOL . PHP_EOL;

            $this->runtimeCollector->addReference(Reference::forDocument($libraryDocument));

            $debugResponse[] = [
                'id' => $libraryDocument->getId(),
                'document' => $documentHierarchyTitle . '/' . $libraryDocument->getTitle(),
                'found_through_vectors' => array_map(
                    static fn (array $vector) => [
                        'distance' => $vector['distance'],
                        'content' => $vector['content'],
                    ],
                    array_filter(
                        $vectors,
                        static fn (array $vector) => $vector['document']->getId() === $libraryDocument->getId(),
                    ),
                ),
            ];
        }

        $this->runtimeCollector->addFunctionDebug(
            new FunctionDebug(
                'library_documents',
                [
                    'search' => $search,
                    'maxDistance' => $maxDistance,
                    'maxResults' => $maxResults,
                    'vector_chunk_count' => count($vectors),
                ],
                $debugResponse,
            ),
        );

        return $result;
    }
}
