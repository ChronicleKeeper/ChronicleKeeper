<?php

declare(strict_types=1);

namespace ChronicleKeeper\Shared\Infrastructure\LLMChain;

use PhpLlm\LlmChain\Bridge\OpenAI\Embeddings;
use PhpLlm\LlmChain\Document\Vector;

use function array_map;
use function assert;
use function is_array;

class EmbeddingCalculator
{
    public function __construct(
        private readonly LLMChainFactory $chainFactory,
    ) {
    }

    /** @return list<float> */
    public function getSingleEmbedding(string $text): array
    {
        $response = $this->chainFactory->createPlatform()->request(
            model: new Embeddings(),
            input: $text,
        )->getContent();
        assert(is_array($response) && $response[0] instanceof Vector);

        return $response[0]->getData();
    }

    /**
     * @param list<string> $texts
     *
     * @return list<list<float>>
     */
    public function getMultipleEmbeddings(array $texts): array
    {
        $platform = $this->chainFactory->createPlatform();

        /** @var list<Vector> $response */
        $response = $platform->request(new Embeddings(), $texts)->getContent();

        /** @var list<list<float>> $embeddings */
        $embeddings = array_map(
            static fn (Vector $vector): array => $vector->getData(),
            $response,
        );

        return $embeddings;
    }
}
