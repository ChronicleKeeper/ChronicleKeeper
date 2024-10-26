<?php

declare(strict_types=1);

namespace ChronicleKeeper\ImageGenerator\Application\Service;

use ChronicleKeeper\ImageGenerator\Domain\Entity\GeneratorResult;
use PhpLlm\LlmChain\OpenAI\Platform;
use RuntimeException;

use function ini_set;

class OpenAIGenerator
{
    public function __construct(
        private readonly Platform $platform,
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

        $response = $this->platform->request('images/generations', $body);

        if (! isset($response['data'][0])) {
            throw new RuntimeException('No image generated.');
        }

        return new GeneratorResult($response['data'][0]['b64_json']);
    }
}
