<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Benchmark\Shared;

use ChronicleKeeper\Shared\Infrastructure\LLMChain\EmbeddingCalculator;
use ChronicleKeeper\Test\Benchmark\UseSymfonyKernel;
use PhpBench\Benchmark\Metadata\Annotations\BeforeMethods;

use function array_fill;
use function assert;
use function str_repeat;

class EmbeddingCalculatorBench
{
    use UseSymfonyKernel;

    private EmbeddingCalculator $embeddingCalculator;

    /** @var list<string> */
    private array $embeddingsToGenerate = [];

    public function setUp(): void
    {
        $kernel = $this->getKernel();

        $embeddingCalculator = $kernel->getContainer()->get(EmbeddingCalculator::class);
        assert($embeddingCalculator instanceof EmbeddingCalculator);
        $this->embeddingCalculator = $embeddingCalculator;

        $this->embeddingsToGenerate = $this->buildEmbeddingsTogenerate();
    }

    /** @BeforeMethods("setUp") */
    public function benchCalculateSingleEmbedding(): void
    {
        foreach ($this->embeddingsToGenerate as $embedding) {
            $this->embeddingCalculator->getSingleEmbedding($embedding);
        }
    }

    /** @BeforeMethods("setUp") */
    public function benchCalculateMultiEmbeddings(): void
    {
        $this->embeddingCalculator->getMultipleEmbeddings($this->embeddingsToGenerate);
    }

    /** @return list<string> */
    private function buildEmbeddingsTogenerate(): array
    {
        $text = str_repeat('This is a test string to generate an embedding for.', 100); // 5100 characters

        return array_fill(0, 2, $text);
    }
}
