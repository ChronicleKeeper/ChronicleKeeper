<?php

declare(strict_types=1);

namespace ChronicleKeeper\Shared\Infrastructure\LLMChain;

use ChronicleKeeper\Shared\Infrastructure\LLMChain\Exception\EmbeddingCalculationFailed;
use PhpLlm\LlmChain\Platform\Bridge\OpenAI\Embeddings;
use PhpLlm\LlmChain\Platform\Vector\Vector;
use Symfony\Component\HttpClient\Exception\ClientException;

use function array_filter;
use function array_map;
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
        return $this->chainFactory->createPlatform()->request(
            model: new Embeddings(),
            input: $text,
        )->asVectors()[0]->getData();
    }

    /**
     * @param list<string> $texts
     *
     * @return list<list<float>>
     */
    public function getMultipleEmbeddings(array $texts): array
    {
        $platform = $this->chainFactory->createPlatform();

        try {
            /** @var list<Vector> $response */
            $response = $platform->request(new Embeddings(), $texts)->asVectors();
        } catch (ClientException $e) {
            // The request contained a bad content, so log knowledge about the falsy content
            throw new EmbeddingCalculationFailed($e);
        }

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

        // In the end filter empty chunks and return the array
        return array_filter($contentChunks, static fn (string $chunk): bool => $chunk !== ''); // @phpstan-ignore return.type
    }
}
