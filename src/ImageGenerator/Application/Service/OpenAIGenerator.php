<?php

declare(strict_types=1);

namespace ChronicleKeeper\ImageGenerator\Application\Service;

use ChronicleKeeper\ImageGenerator\Domain\Entity\GeneratorResult;
use ChronicleKeeper\Shared\Infrastructure\LLMChain\LLMChainFactory;
use PhpLlm\LlmChain\Platform\Bridge\OpenAI\DallE;
use PhpLlm\LlmChain\Platform\Bridge\OpenAI\DallE\Base64Image;
use PhpLlm\LlmChain\Platform\Bridge\OpenAI\DallE\ImageResponse;

use function assert;
use function count;
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
            model: new DallE(name: DallE::DALL_E_3),
            input: $prompt,
            options: ['response_format' => 'b64_json'],
        )->getResponse();

        assert($response instanceof ImageResponse);
        $images = $response->getContent();

        assert(count($images) === 1); // Always only a single image cause of Dalle3
        assert($images[0] instanceof Base64Image);

        return new GeneratorResult($images[0]->encodedImage, (string) $response->revisedPrompt);
    }
}
