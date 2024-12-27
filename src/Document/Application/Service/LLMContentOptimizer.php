<?php

declare(strict_types=1);

namespace ChronicleKeeper\Document\Application\Service;

use ChronicleKeeper\Settings\Application\Service\SystemPromptRegistry;
use ChronicleKeeper\Settings\Domain\ValueObject\SystemPrompt\Purpose;
use ChronicleKeeper\Shared\Infrastructure\LLMChain\LLMChainFactory;
use PhpLlm\LlmChain\Bridge\OpenAI\GPT;
use PhpLlm\LlmChain\Model\Message\Message;
use PhpLlm\LlmChain\Model\Message\MessageBag;
use PhpLlm\LlmChain\Model\Response\TextResponse;

use function assert;

class LLMContentOptimizer
{
    public function __construct(
        private readonly LLMChainFactory $llmChainFactory,
        private readonly SystemPromptRegistry $promptRegistry,
    ) {
    }

    public function optimize(string $content): string
    {
        $prompt = $this->promptRegistry->getDefaultForPurpose(Purpose::DOCUMENT_OPTIMIZER);

        $response = $this->llmChainFactory->create()->call(
            new MessageBag(
                Message::forSystem($prompt->getContent()),
                Message::ofUser($content),
            ),
            ['model' => GPT::GPT_4O, 'temperature' => 0.75],
        );

        assert($response instanceof TextResponse);

        return $response->getContent();
    }
}
