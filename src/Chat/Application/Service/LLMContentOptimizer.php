<?php

declare(strict_types=1);

namespace ChronicleKeeper\Chat\Application\Service;

use ChronicleKeeper\Shared\Infrastructure\LLMChain\LLMChainFactory;
use PhpLlm\LlmChain\Message\Message;
use PhpLlm\LlmChain\Message\MessageBag;
use PhpLlm\LlmChain\Message\SystemMessage;
use PhpLlm\LlmChain\OpenAI\Model\Gpt\Version;
use PhpLlm\LlmChain\Response\Response;

use function assert;
use function is_object;

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
            ['model' => Version::gpt4oMini()->name, 'temperature' => 0.75],
        );

        if (is_object($response)) {
            assert($response instanceof Response);

            return (string) $response->getContent();
        }

        return $response;
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
