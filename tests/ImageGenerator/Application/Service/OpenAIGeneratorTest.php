<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\ImageGenerator\Application\Service;

use ChronicleKeeper\ImageGenerator\Application\Service\OpenAIGenerator;
use ChronicleKeeper\Shared\Infrastructure\LLMChain\LLMChainFactory;
use ChronicleKeeper\Test\Shared\Infrastructure\LLMChain\ResponsePromiseFactory;
use PhpLlm\LlmChain\Platform\Bridge\OpenAI\DallE;
use PhpLlm\LlmChain\Platform\Bridge\OpenAI\DallE\Base64Image;
use PhpLlm\LlmChain\Platform\Bridge\OpenAI\DallE\ImageResponse;
use PhpLlm\LlmChain\Platform\PlatformInterface;
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
        $response = new ImageResponse('bar', new Base64Image('foo'));

        $platform = $this->createMock(PlatformInterface::class);
        $platform
            ->expects($this->once())
            ->method('request')
            ->with(
                self::isInstanceOf(DallE::class),
                'bar',
            )
            ->willReturn(ResponsePromiseFactory::create($response));

        $chainFactory = $this->createMock(LLMChainFactory::class);
        $chainFactory->expects($this->once())
            ->method('createPlatform')
            ->willReturn($platform);

        $generatorResult = (new OpenAIGenerator($chainFactory))->generate('bar');

        self::assertSame('foo', $generatorResult->encodedImage);
        self::assertSame('bar', $generatorResult->revisedPrompt);
    }
}
