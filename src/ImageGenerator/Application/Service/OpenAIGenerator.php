<?php

declare(strict_types=1);

namespace ChronicleKeeper\ImageGenerator\Application\Service;

use ChronicleKeeper\ImageGenerator\Application\Service\OpenAIGenerator\ResponseImage;
use PhpLlm\LlmChain\OpenAI\Model\Gpt;
use PhpLlm\LlmChain\OpenAI\Platform;
use PhpLlm\LlmChain\OpenAI\Platform\OpenAI;

use function array_merge;
use function base64_decode;

class OpenAIGenerator
{
    private const string MODEL_DALL_E_2 = 'dall-e-2';
    private const string MODEL_DALL_E_3 = 'dall-e-3';

    public function __construct(
        private readonly Platform $platform,
    )
    {

    }

    public function generate(string $prompt): array
    {

        // Initiales Generieren des Bildes

        $body = [
            'prompt' => $prompt,
            'model' => self::MODEL_DALL_E_2,
            'response_format' => 'b64_json',
        ];

        $response = $this->platform->request('images/generations', $body);

        $images = [];
        foreach ($response['data'] as $responseImage) {
            $images[] = new ResponseImage('image/png', $responseImage['b64_json']);
        }

        // Bearbeiten des Bildes
        foreach ($images as $index => $image) {
            $body = [
                'prompt' => 'Färbe das Bild in Schwarzweiß. Eine Fee soll in der rechten oberen Ecke lächelnd auf den Helden herabsehen.',
                'image' => $image->encodedImage,
                'model' => self::MODEL_DALL_E_2,
                'response_format' => 'b64_json',
            ];

            $response = $this->platform->request('images/edits', $body);
            dump($response);

            foreach ($response['data'] as $responseImage) {
                $images[$index] = new ResponseImage('image/png', $responseImage['b64_json']);
            }
        }

        return $images;
    }
}
