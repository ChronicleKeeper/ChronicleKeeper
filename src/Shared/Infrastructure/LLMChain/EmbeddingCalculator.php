<?php

declare(strict_types=1);

namespace ChronicleKeeper\Shared\Infrastructure\LLMChain;

use PhpLlm\LlmChain\Bridge\OpenAI\Embeddings;
use PhpLlm\LlmChain\Document\Vector;

use function array_map;
use function assert;
use function is_array;
use function strlen;
use function substr;
use function Symfony\Component\String\u;
use function trim;

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

    /** @return list<string> */
    public function createTextChunks(string $content, int $chunkLength = 800, int $minChunkLength = 50): array
    {
        $contentChunks = [];
        do {
            $vectorContent     = u($content)->truncate($chunkLength, '', false)->toString();
            $leftContent       = trim(substr($content, strlen($vectorContent)));
            $leftContentLength = strlen($leftContent);

            if ($leftContentLength < $minChunkLength || $leftContentLength < $chunkLength / 2) {
                // If there is too less content left utilize the full left content and end loop
                $vectorContent = $content;
                $leftContent   = '';
            }

            // Set the content to the full left content
            $content = $leftContent;

            // Now add the chunk to the array
            $contentChunks[] = $vectorContent;
        } while ($content !== '');

        return $contentChunks;
    }
}
