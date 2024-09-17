<?php

declare(strict_types=1);

namespace DZunke\NovDoc\Infrastructure\LLMChainExtension\Tool;

use DZunke\NovDoc\Domain\Document\Document;
use DZunke\NovDoc\Domain\Settings\SettingsHandler;
use DZunke\NovDoc\Infrastructure\Repository\FilesystemVectorDocumentRepository;
use PhpLlm\LlmChain\EmbeddingModel;
use PhpLlm\LlmChain\ToolBox\AsTool;

use function count;

use const PHP_EOL;

#[AsTool(
    'novalis_background',
    description: 'Delivers all background information to the world of novalis or characters living in the world. For detailied visual information utilize function "novalis_images".',
)]
final class NovalisBackground
{
    /** @var list<Document> */
    private array $referencedDocuments = [];

    public function __construct(
        private readonly FilesystemVectorDocumentRepository $vectorDocumentRepository,
        private readonly EmbeddingModel $embeddings,
        private readonly SettingsHandler $settingsHandler,
    ) {
    }

    /** @param string $search Contains the question or message the user has sent in reference to novalis. */
    public function __invoke(string $search): string
    {
        $vector    = $this->embeddings->create($search);
        $documents = $this->vectorDocumentRepository->findSimilar(
            $vector->getData(),
            maxResults: $this->settingsHandler->get()->getChatbotGeneral()->getMaxDocumentResponses(),
        );

        $this->referencedDocuments = [];
        if (count($documents) === 0) {
            return 'There are no matching documents.';
        }

        $result = 'I have found the following information that are associated to the world of Novalis:' . PHP_EOL;
        foreach ($documents as $document) {
            $result .= '# Title: ' . $document->document->title . PHP_EOL;
            $result .= $document->document->content;

            if ($this->settingsHandler->get()->getChatbotGeneral()->showReferencedDocuments() !== true) {
                continue;
            }

            $this->referencedDocuments[] = $document->document;
        }

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
