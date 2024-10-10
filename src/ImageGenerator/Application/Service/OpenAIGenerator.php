<?php

declare(strict_types=1);

namespace ChronicleKeeper\ImageGenerator\Application\Service;

use ChronicleKeeper\ImageGenerator\Domain\Entity\GeneratorResult;
use PhpLlm\LlmChain\OpenAI\Platform;
use RuntimeException;

use function ini_set;

class OpenAIGenerator
{
    private const DALL_E_PROMT_GENERATOR_PROMT = <<<'TEXT'
    You are an assistant to the user who tries to find out a perfect system prompt to hand over to Dall E Image generation.
    You will split the users message into persons, locations and other helpful pieces where more knowledge will be helpful
    to generate a perfect image that fits the users request.

    For each split you will call the function "library_documents".
    For each split you will call the function "library_images".

    You will enhance the given users prompt with the function responses in a way it describes the wanted image as detailed as possible.

    Within your response you will:
    - Give a visual description for characters that is as detailed as possible.
    - Give a visual description for locations that is as detailed as possible.
    - Give a base64 encoded image if found one with library_images

    Your answers will be optimized to a utilization of the image generation model dall-e-3.
    Your answer will not contain an explanation of what you have done, so just the required prompt for generating the image.
    Your answer will be formatted in markdown.
    Your answer will be in the language of the users request.
    TEXT;

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
            throw new RuntimeException('No image generated');
        }

        return new GeneratorResult($response['data'][0]['b64_json']);
    }
}
