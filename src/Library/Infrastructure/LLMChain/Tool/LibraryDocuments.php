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
    description: 'Delivers all background information to the world of novalis or characters living in the world. For detailied visual information utilize function "library_images".',
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

    /** @param string $search Contains the question or message the user has sent in reference to novalis. */
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

        $result = 'I have found the following information that are associated to the world of Novalis:' . PHP_EOL;
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
