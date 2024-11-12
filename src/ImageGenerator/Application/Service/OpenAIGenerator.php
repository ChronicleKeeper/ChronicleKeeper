<?php

declare(strict_types=1);

namespace ChronicleKeeper\ImageGenerator\Application\Service;

use ChronicleKeeper\ImageGenerator\Domain\Entity\GeneratorResult;
use ChronicleKeeper\Shared\Infrastructure\LLMChain\LLMChainFactory;
use RuntimeException;

use function ini_set;
use function is_array;

class OpenAIGenerator
{
    public function __construct(
        private readonly LLMChainFactory $llmChainFactory,
    ) {
    }

    public function generate(string $prompt): GeneratorResult
    {
        // Set unlimited timeout in case it is not done in php.ini, sometimes it take a lot of time
        ini_set('default_socket_timeout', -1);

        $body = [
            'prompt' => $prompt,
            'model' => 'dall-e-3',
            'response_format' => 'b64_json',
        ];

        $response = $this->llmChainFactory->createPlatform()->request('images/generations', $body);

        if (! is_array($response) || ! isset($response['data'][0])) {
            throw new RuntimeException('No image generated.');
        }

        return new GeneratorResult($response['data'][0]['b64_json']);
    }
}
