<?php

declare(strict_types=1);

namespace DZunke\NovDoc\Domain\LLMExtension\Tool;

use DZunke\NovDoc\Domain\Settings\SettingsHandler;
use DZunke\NovDoc\Infrastructure\Repository\FilesystemVectorDocumentRepository;
use PhpLlm\LlmChain\EmbeddingModel;
use PhpLlm\LlmChain\ToolBox\AsTool;

use function count;

use const PHP_EOL;

#[AsTool(
    'novalis_background',
    description: 'Delivers all background information to the world of novalis or characters living in the world.',
)]
final class NovalisBackground
{
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
        $documents = $this->vectorDocumentRepository->findSimilarDocuments(
            $vector->getData(),
            maxResults: $this->settingsHandler->get()->maxDocumentResponses,
        );

        if (count($documents) === 0) {
            return 'There are no matching documents.';
        }

        $result = 'I have found the following information that are associated to the world of Novalis:' . PHP_EOL;
        foreach ($documents as $document) {
            $result .= '# Title: ' . $document->document->title . PHP_EOL;
            $result .= $document->document->content;
        }

        return $result;
    }
}
