<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\ImageGenerator\Application\Service;

use ChronicleKeeper\ImageGenerator\Application\Service\OpenAIGenerator;
use ChronicleKeeper\ImageGenerator\Infrastructure\LLMChain\Bridge\OpenAI\DallE;
use ChronicleKeeper\ImageGenerator\Infrastructure\LLMChain\Bridge\OpenAI\DallE\Base64ImageResponse;
use ChronicleKeeper\Shared\Infrastructure\LLMChain\LLMChainFactory;
use PhpLlm\LlmChain\PlatformInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(OpenAIGenerator::class)]
#[Small]
class OpenAIGeneratorTest extends TestCase
{
    #[Test]
    public function base64ImageWillBeReturned(): void
    {
        $platform = $this->createMock(PlatformInterface::class);
        $platform
            ->expects($this->once())
            ->method('request')
            ->with(
                self::isInstanceOf(DallE::class),
                'bar',
            )
            ->willReturn(new Base64ImageResponse('bar', 'foo'));

        $chainFactory = $this->createMock(LLMChainFactory::class);
        $chainFactory->expects($this->once())
            ->method('createPlatform')
            ->willReturn($platform);

        $generatorResult = (new OpenAIGenerator($chainFactory))->generate('bar');

        self::assertSame('foo', $generatorResult->encodedImage);
        self::assertSame('bar', $generatorResult->revisedPrompt);
    }
}
