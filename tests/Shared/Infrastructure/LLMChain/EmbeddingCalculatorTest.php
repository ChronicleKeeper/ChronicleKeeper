<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Shared\Infrastructure\LLMChain;

use ChronicleKeeper\Shared\Infrastructure\LLMChain\EmbeddingCalculator;
use ChronicleKeeper\Shared\Infrastructure\LLMChain\Exception\EmbeddingCalculationFailed;
use ChronicleKeeper\Shared\Infrastructure\LLMChain\LLMChainFactory;
use PhpLlm\LlmChain\Document\Vector;
use PhpLlm\LlmChain\Model\Response\VectorResponse;
use PhpLlm\LlmChain\PlatformInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\Exception\ClientException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\HttpClient\ResponseInterface;

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

    #[Test]
    public function itCreatesTextChunksOfSpezificSize(): void
    {
        $calculator = new EmbeddingCalculator(self::createStub(LLMChainFactory::class));

        $chunks = $calculator->createTextChunks('This is a test string to generate an embedding for.', 10, 1);

        self::assertCount(4, $chunks);

        self::assertSame('This is a', $chunks[0]);
        self::assertSame('test string', $chunks[1]);
        self::assertSame('to generate', $chunks[2]);
        self::assertSame('an embedding for.', $chunks[3]);
    }

    #[Test]
    public function itCreatesTextChunksOfSpezificSizeWithMinChunkLength(): void
    {
        $calculator = new EmbeddingCalculator(self::createStub(LLMChainFactory::class));

        $chunks = $calculator->createTextChunks('This is a test string to generate an embedding for.', 10, 100);

        // Just a single chunk because the minChunkLength is higher than the content length
        self::assertCount(1, $chunks);
        self::assertSame('This is a test string to generate an embedding for.', $chunks[0]);
    }

    #[Test]
    public function itIsFilteringEmptyTextChunks(): void
    {
        $calculator = new EmbeddingCalculator(self::createStub(LLMChainFactory::class));

        $chunks = $calculator->createTextChunks('', 10, 100);

        // Just a single chunk because the minChunkLength is higher than the content length
        self::assertCount(0, $chunks);
    }

    #[Test]
    public function itIsCatchingAClientException(): void
    {
        $this->expectException(EmbeddingCalculationFailed::class);

        $response = self::createStub(ResponseInterface::class);
        $response->method('getStatusCode')->willReturn(Response::HTTP_BAD_REQUEST);
        $response->method('getContent')->willReturn('Bad Request');
        $response->method('getInfo')->willReturnCallback(
            static fn (string $name) => match ($name) {
                'http_code' => Response::HTTP_BAD_REQUEST,
                'url' => 'https://api.example.com/embeddings',
                'response_headers' => [
                    'HTTP/1.1 400 Bad Request',
                    'Content-Type: application/json',
                ],
                default => null,
            },
        );

        $platform = self::createStub(PlatformInterface::class);
        $platform->method('request')->willThrowException(new ClientException($response));

        $llmChainFactory = self::createStub(LLMChainFactory::class);
        $llmChainFactory->method('createPlatform')->willReturn($platform);

        $calculator = new EmbeddingCalculator($llmChainFactory);

        $calculator->getMultipleEmbeddings(['This is a test string to generate an embedding for.']);
    }
}
