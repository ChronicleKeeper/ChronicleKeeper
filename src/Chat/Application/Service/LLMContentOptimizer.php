<?php

declare(strict_types=1);

namespace DZunke\NovDoc\Chat\Application\Service;

use PhpLlm\LlmChain\LanguageModel;
use PhpLlm\LlmChain\Message\Message;
use PhpLlm\LlmChain\Message\MessageBag;
use PhpLlm\LlmChain\OpenAI\Model\Gpt\Version;

class LLMContentOptimizer
{
    public function __construct(
        private readonly LanguageModel $llm,
    ) {
    }

    public function optimize(string $content): string
    {
        $response = $this->llm->call(
            new MessageBag($this->getSystemPrompt(), Message::ofUser($content)),
            ['model' => Version::gpt4oMini()->name, 'temperature' => 0.75],
        );

        return (string) $response->getContent();
    }

    private function getSystemPrompt(): Message
    {
        return Message::forSystem(<<<'TEXT'
        You are a proof reader and will simnply correct and reformat text to markdown. Where it is recommended
        you will add formattings to the text. The response will be given in plain markdown.
        TEXT);
    }
}
