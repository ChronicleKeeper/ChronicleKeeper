<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Shared\Infrastructure\LLMChain;

use ChronicleKeeper\Shared\Infrastructure\LLMChain\EmbeddingCalculator;
use ChronicleKeeper\Shared\Infrastructure\LLMChain\LLMChainFactory;
use PhpLlm\LlmChain\Document\Vector;
use PhpLlm\LlmChain\Model\Response\VectorResponse;
use PhpLlm\LlmChain\PlatformInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(EmbeddingCalculator::class)]
#[Small]
class EmbeddingCalculatorTest extends TestCase
{
    #[Test]
    public function itGeneratesASingleEmbedding(): void
    {
        $response = new VectorResponse(new Vector([0.1, 0.2, 0.3]));

        $platform = self::createStub(PlatformInterface::class);
        $platform->method('request')->willReturn($response);

        $llmChainFactory = self::createStub(LLMChainFactory::class);
        $llmChainFactory->method('createPlatform')->willReturn($platform);

        $calculator = new EmbeddingCalculator($llmChainFactory);

        $embedding = $calculator->getSingleEmbedding('This is a test string to generate an embedding for.');

        self::assertSame([0.1, 0.2, 0.3], $embedding);
    }

    #[Test]
    public function itGeneratesMultipleEmbeddings(): void
    {
        $response = new VectorResponse(
            new Vector([0.1, 0.2, 0.3]),
            new Vector([0.3, 0.4, 0.5]),
        );

        $platform = self::createStub(PlatformInterface::class);
        $platform->method('request')->willReturn($response);

        $llmChainFactory = self::createStub(LLMChainFactory::class);
        $llmChainFactory->method('createPlatform')->willReturn($platform);

        $calculator = new EmbeddingCalculator($llmChainFactory);

        $embeddings = $calculator->getMultipleEmbeddings([
            'This is a test string to generate an embedding for.',
            'This is another test string to generate an embedding for.',
        ]);

        self::assertSame(
            [
                [0.1, 0.2, 0.3],
                [0.3, 0.4, 0.5],
            ],
            $embeddings,
        );
    }
}
