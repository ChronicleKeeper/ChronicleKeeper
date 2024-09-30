<?php

declare(strict_types=1);

namespace ChronicleKeeper\Library\Infrastructure\LLMChain\Tool;

use ChronicleKeeper\Library\Domain\Entity\Document;
use ChronicleKeeper\Library\Infrastructure\Repository\FilesystemVectorDocumentRepository;
use ChronicleKeeper\Settings\Application\SettingsHandler;
use ChronicleKeeper\Shared\Infrastructure\LLMChain\ToolUsageCollector;
use PhpLlm\LlmChain\EmbeddingsModel;
use PhpLlm\LlmChain\ToolBox\AsTool;

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
final class LibraryDocuments
{
    /** @var list<Document> */
    private array $referencedDocuments = [];

    private float|null $maxDistance = null;

    public function __construct(
        private readonly FilesystemVectorDocumentRepository $vectorDocumentRepository,
        private readonly EmbeddingsModel $embeddings,
        private readonly SettingsHandler $settingsHandler,
        private readonly ToolUsageCollector $collector,
    ) {
    }

    public function setOneTimeMaxDistance(float $maxDistance): void
    {
        $this->maxDistance = $maxDistance;
    }

    /** @param string $search The search parameter containing the user's question or relevant keywords related to the information they seek */
    public function __invoke(string $search): string
    {
        $maxResults = $this->settingsHandler->get()->getChatbotGeneral()->getMaxDocumentResponses();

        $vector    = $this->embeddings->create($search);
        $documents = $this->vectorDocumentRepository->findSimilar(
            $vector->getData(),
            maxDistance: $this->maxDistance,
            maxResults: $maxResults,
        );

        $this->referencedDocuments = [];
        if (count($documents) === 0) {
            return 'There are no matching documents.';
        }

        $debugResponse = [];

        $result = 'I have found the following information that are associated to the question:' . PHP_EOL;
        foreach ($documents as $document) {
            $libraryDocument = $document['vector']->document;

            $result .= '# Title: ' . $libraryDocument->title . PHP_EOL;
            $result .= 'Storage Directory: ' . $libraryDocument->directory->flattenHierarchyTitle() . PHP_EOL;
            $result .= $libraryDocument->content . PHP_EOL . PHP_EOL;

            $this->referencedDocuments[] = $libraryDocument;

            $debugResponse[] = [
                'document' => $libraryDocument->directory->flattenHierarchyTitle()
                    . '/' . $libraryDocument->title,
                'distance' => $document['distance'],
            ];
        }

        $this->collector->called(
            'library_documents',
            [
                'arguments' => ['search' => $search, 'maxDistance' => $this->maxDistance, 'maxResults' => $maxResults],
                'responses' => $debugResponse,
            ],
        );

        $this->maxDistance = null;

        return $result;
    }

    /** @return list<Document> */
    public function getReferencedDocuments(): array
    {
        $documents                 = $this->referencedDocuments;
        $this->referencedDocuments = [];

        return $documents;
    }
}
