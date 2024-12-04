<?php

declare(strict_types=1);

namespace ChronicleKeeper\Shared\Infrastructure\LLMChain;

use PhpLlm\LlmChain\Bridge\OpenAI\Embeddings;
use PhpLlm\LlmChain\Model\Response\AsyncResponse;
use PhpLlm\LlmChain\Model\Response\VectorResponse;

use function assert;

class EmbeddingCalculator
{
    public function __construct(
        private readonly LLMChainFactory $chainFactory,
    ) {
    }

    /** @return list<float> */
    public function getSingleEmbedding(string $text): array
    {
        $vector = $this->chainFactory->createPlatform()->request(
            model: new Embeddings(),
            input: $text,
        );

        assert($vector instanceof AsyncResponse);
        $vector = $vector->unwrap();

        assert($vector instanceof VectorResponse);
        $vector = $vector->getContent()[0];

        return $vector->getData();
    }
}
