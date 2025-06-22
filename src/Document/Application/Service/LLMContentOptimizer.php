<?php

declare(strict_types=1);

namespace ChronicleKeeper\Document\Application\Service;

use ChronicleKeeper\Settings\Domain\Entity\SystemPrompt;
use ChronicleKeeper\Shared\Infrastructure\LLMChain\LLMChainFactory;
use PhpLlm\LlmChain\Platform\Bridge\OpenAI\GPT;
use PhpLlm\LlmChain\Platform\Message\Message;
use PhpLlm\LlmChain\Platform\Message\MessageBag;
use PhpLlm\LlmChain\Platform\Response\TextResponse;

use function assert;

class LLMContentOptimizer
{
    public function __construct(
        private readonly LLMChainFactory $llmChainFactory,
    ) {
    }

    public function optimize(SystemPrompt $systemPrompt, string $content): string
    {
        $response = $this->llmChainFactory->create()->call(
            new MessageBag(
                Message::forSystem($systemPrompt->getContent()),
                Message::ofUser($content),
            ),
            ['model' => GPT::GPT_4O, 'temperature' => 0.75],
        );

        assert($response instanceof TextResponse);

        return $response->getContent();
    }
}
