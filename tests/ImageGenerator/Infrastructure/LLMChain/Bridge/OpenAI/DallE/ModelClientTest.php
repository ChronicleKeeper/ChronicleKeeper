<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\ImageGenerator\Infrastructure\LLMChain\Bridge\OpenAI\DallE;

use ChronicleKeeper\ImageGenerator\Infrastructure\LLMChain\Bridge\OpenAI\DallE;
use ChronicleKeeper\ImageGenerator\Infrastructure\LLMChain\Bridge\OpenAI\DallE\ModelClient;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;
use Webmozart\Assert\InvalidArgumentException;

#[CoversClass(ModelClient::class)]
#[Small]
class ModelClientTest extends TestCase
{
    #[Test]
    public function instantiationIsNotPossibleWithEmptyApiKey(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The API key must not be empty.');

        new ModelClient(self::createStub(HttpClientInterface::class), '');
    }

    #[Test]
    public function instantiationIsNotPossibleWithInvalidApiKey(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The API key must start with "sk-".');

        new ModelClient(self::createStub(HttpClientInterface::class), 'invalid-api-key');
    }

    #[Test]
    public function supportsDallEModel(): void
    {
        $modelClient = new ModelClient(self::createStub(HttpClientInterface::class), 'sk-foo');
        self::assertTrue($modelClient->supports(new DallE(), 'foo'));
    }

    #[Test]
    public function requestWillBeExecuted(): void
    {
        $httpClient = $this->createMock(HttpClientInterface::class);
        $httpClient->expects($this->once())
            ->method('request')
            ->with('POST', 'https://api.openai.com/v1/images/generations', [
                'auth_bearer' => 'sk-foo',
                'json' => [
                    'model' => DallE::DALL_E_3,
                    'prompt' => 'foo',
                ],
            ]);

        $modelClient = new ModelClient($httpClient, 'sk-foo');
        $modelClient->request(new DallE(), 'foo');
    }

    #[Test]
    public function convertWillReturnBase64ImageResponse(): void
    {
        $httpClient  = self::createStub(HttpClientInterface::class);
        $modelClient = new ModelClient($httpClient, 'sk-foo');

        $httpResponse = self::createStub(ResponseInterface::class);
        $httpResponse->method('toArray')
            ->willReturn([
                'data' => [
                    [
                        'revised_prompt' => 'revised-prompt',
                        'b64_json'       => 'image',
                    ],
                ],
            ]);

        $response = $modelClient->convert($httpResponse);

        self::assertSame('image', $response->getContent());
    }

    #[Test]
    public function convertWillThrowExceptionIfNoImageGenerated(): void
    {
        $this->expectExceptionMessage('No image generated.');
        $this->expectException(RuntimeException::class);

        $httpClient  = self::createStub(HttpClientInterface::class);
        $modelClient = new ModelClient($httpClient, 'sk-foo');

        $httpResponse = self::createStub(ResponseInterface::class);
        $httpResponse->method('toArray')
            ->willReturn([
                'data' => [],
            ]);

        $modelClient->convert($httpResponse);
    }
}
