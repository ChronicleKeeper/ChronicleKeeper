<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\ImageGenerator\Application\Service;

use ChronicleKeeper\ImageGenerator\Application\Service\OpenAIGenerator;
use ChronicleKeeper\Shared\Infrastructure\LLMChain\LLMChainFactory;
use PhpLlm\LlmChain\OpenAI\Platform;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use RuntimeException;

#[CoversClass(OpenAIGenerator::class)]
#[Small]
class OpenAIGeneratorTest extends TestCase
{
    #[Test]
    public function requestHasNoImageResultsInException(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('No image generated.');

        $platform = $this->createMock(Platform::class);
        $platform
            ->expects($this->once())
            ->method('request')
            ->willReturn(['data' => []]);

        $chainFactory = $this->createMock(LLMChainFactory::class);
        $chainFactory->expects($this->once())
            ->method('createPlatform')
            ->willReturn($platform);

        (new OpenAIGenerator($chainFactory))->generate('foo');
    }

    #[Test]
    public function base64ImageWillBeReturned(): void
    {
        $platform = $this->createMock(Platform::class);
        $platform
            ->expects($this->once())
            ->method('request')
            ->with(
                'images/generations',
                ['prompt' => 'bar', 'model' => 'dall-e-3', 'response_format' => 'b64_json'],
            )
            ->willReturn(['data' => [['b64_json' => 'foo']]]);

        $chainFactory = $this->createMock(LLMChainFactory::class);
        $chainFactory->expects($this->once())
            ->method('createPlatform')
            ->willReturn($platform);

        $generatorResult = (new OpenAIGenerator($chainFactory))->generate('bar');

        self::assertSame('foo', $generatorResult->encodedImage);
    }
}
