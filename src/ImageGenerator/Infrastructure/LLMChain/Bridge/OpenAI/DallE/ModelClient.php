<?php

declare(strict_types=1);

namespace ChronicleKeeper\ImageGenerator\Infrastructure\LLMChain\Bridge\OpenAI\DallE;

use ChronicleKeeper\ImageGenerator\Infrastructure\LLMChain\Bridge\OpenAI\DallE;
use PhpLlm\LlmChain\Model\Model;
use PhpLlm\LlmChain\Model\Response\ResponseInterface as LlmResponse;
use PhpLlm\LlmChain\Platform\ModelClient as PlatformResponseFactory;
use PhpLlm\LlmChain\Platform\ResponseConverter as PlatformResponseConverter;
use RuntimeException;
use SensitiveParameter;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface as HttpResponse;
use Webmozart\Assert\Assert;

use function array_merge;

final readonly class ModelClient implements PlatformResponseFactory, PlatformResponseConverter
{
    public function __construct(
        private HttpClientInterface $httpClient,
        #[SensitiveParameter]
        private string $apiKey,
    ) {
        Assert::stringNotEmpty($apiKey, 'The API key must not be empty.');
        Assert::startsWith($apiKey, 'sk-', 'The API key must start with "sk-".');
    }

    public function supports(Model $model, array|string|object $input): bool
    {
        return $model instanceof DallE;
    }

    /** @inheritdoc */
    public function request(Model $model, object|array|string $input, array $options = []): HttpResponse
    {
        return $this->httpClient->request('POST', 'https://api.openai.com/v1/images/generations', [
            'auth_bearer' => $this->apiKey,
            'json' => array_merge($options, [
                'model' => $model->getVersion(),
                'prompt' => $input,
            ]),
        ]);
    }

    /** @inheritdoc */
    public function convert(HttpResponse $response, array $options = []): LlmResponse
    {
        $response = $response->toArray();
        if (! isset($response['data'][0])) {
            throw new RuntimeException('No image generated.');
        }

        $image = $response['data'][0];

        return new Base64ImageResponse($image['revised_prompt'], $image['b64_json']);
    }
}
