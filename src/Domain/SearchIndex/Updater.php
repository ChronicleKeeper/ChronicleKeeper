<?php

declare(strict_types=1);

namespace DZunke\NovDoc\Domain\SearchIndex;

use DZunke\NovDoc\Domain\Prompts\PromptBag;
use PhpLlm\LlmChain\Chat;
use PhpLlm\LlmChain\Document\Document;
use PhpLlm\LlmChain\DocumentEmbedder;
use PhpLlm\LlmChain\Message\Message;
use PhpLlm\LlmChain\Message\MessageBag;

use const PHP_EOL;

class Updater
{
    public function __construct(
        private readonly Chat $chat,
        private readonly DocumentEmbedder $documentEmbedder,
    ) {
    }

    public function update(DocumentBag $documents, PromptBag $prompts): void
    {
        foreach ($documents as $document) {
            // Vectorize the document itself
            $this->documentEmbedder->embed([$document]);

            foreach ($prompts as $prompt) {
                $requestedText = $prompt->prompt . PHP_EOL . PHP_EOL . $document->text;
                $promptResult  = $this->chat->call(new MessageBag(Message::ofUser($requestedText)));

                dump($prompt->prompt);
                dump($promptResult);

                $this->documentEmbedder->embed([Document::fromText($promptResult)]);
            }
        }
    }
}
