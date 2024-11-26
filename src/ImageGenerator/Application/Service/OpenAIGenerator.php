<?php

declare(strict_types=1);

namespace ChronicleKeeper\ImageGenerator\Application\Service;

use ChronicleKeeper\ImageGenerator\Domain\Entity\GeneratorResult;
use ChronicleKeeper\ImageGenerator\Infrastructure\LLMChain\Bridge\OpenAI\DallE;
use ChronicleKeeper\ImageGenerator\Infrastructure\LLMChain\Bridge\OpenAI\DallE\Base64ImageResponse;
use ChronicleKeeper\Shared\Infrastructure\LLMChain\LLMChainFactory;
use PhpLlm\LlmChain\Model\Response\AsyncResponse;

use function assert;
use function ini_set;

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

        $response = $this->llmChainFactory->createPlatform()->request(
            model: new DallE(),
            input: $prompt,
        );

        if ($response instanceof AsyncResponse) {
            $response = $response->unwrap();
        }

        assert($response instanceof Base64ImageResponse);

        return new GeneratorResult($response->image, $response->revisedPrompt);
    }
}
