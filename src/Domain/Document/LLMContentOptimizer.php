<?php

declare(strict_types=1);

namespace DZunke\NovDoc\Domain\Document;

use PhpLlm\LlmChain\LanguageModel;
use PhpLlm\LlmChain\Message\Message;
use PhpLlm\LlmChain\Message\MessageBag;
use PhpLlm\LlmChain\OpenAI\Model\Gpt\Version;

use function is_array;
use function is_string;

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
            ['model' => Version::GPT_4o_MINI, 'temperature' => 0.75],
        );

        $choices = $response['choices'];
        if (! isset($choices) || ! is_array($choices)) {
            return $content;
        }

        $firstChoice = $choices[0];
        if (! isset($firstChoice['message']) || ! is_array($firstChoice['message'])) {
            return $content;
        }

        $message = $firstChoice['message'];
        if (! isset($message['content']) || ! is_string($message['content'])) {
            return $content;
        }

        return $message['content'];
    }

    private function getSystemPrompt(): Message
    {
        return Message::forSystem(<<<'TEXT'
        You are a proof reader and will simnply correct and reformat text to markdown. Where it is recommended
        you will add formattings to the text. The response will be given in plain markdown.
        TEXT);
    }
}
