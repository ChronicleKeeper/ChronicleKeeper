<?php

declare(strict_types=1);

namespace ChronicleKeeper\Chat\Application\Service;

use ChronicleKeeper\Shared\Infrastructure\LLMChain\LLMChainFactory;
use PhpLlm\LlmChain\Bridge\OpenAI\GPT;
use PhpLlm\LlmChain\Model\Message\Message;
use PhpLlm\LlmChain\Model\Message\MessageBag;
use PhpLlm\LlmChain\Model\Message\SystemMessage;
use PhpLlm\LlmChain\Model\Response\TextResponse;

use function assert;

class LLMContentOptimizer
{
    public function __construct(
        private readonly LLMChainFactory $llmChainFactory,
    ) {
    }

    public function optimize(string $content): string
    {
        $response = $this->llmChainFactory->create()->call(
            new MessageBag($this->getSystemPrompt(), Message::ofUser($content)),
            ['model' => GPT::GPT_4O, 'temperature' => 0.75],
        );

        assert($response instanceof TextResponse);

        return $response->getContent();
    }

    private function getSystemPrompt(): SystemMessage
    {
        return Message::forSystem(<<<'TEXT'
        You are a proof reader and will simnply correct and reformat text to plain markdown. Where it is recommended
        you will add formattings to the text. The response will be given in plain markdown without escape ticks around
        the response.
        TEXT);
    }
}
