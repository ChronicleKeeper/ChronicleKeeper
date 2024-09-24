<?php

declare(strict_types=1);

namespace ChronicleKeeper\Library\Application\Service\Image;

use ChronicleKeeper\Library\Domain\Entity\Image;
use ChronicleKeeper\Settings\Application\SettingsHandler;
use PhpLlm\LlmChain\Message\Role;
use PhpLlm\LlmChain\OpenAI\Model\Gpt\Version;
use PhpLlm\LlmChain\OpenAI\Runtime;

use function is_array;
use function is_string;

class LLMDescriber
{
    public function __construct(
        private readonly Runtime $runtime,
        private readonly SettingsHandler $settingsHandler,
    ) {
    }

    public function getDescription(Image $image): string
    {
        $settings = $this->settingsHandler->get();

        $messages = [
            [
                'role' => Role::System->value,
                'content' => $settings->getChatbotSystemPrompt()->getSystemPrompt(),
            ],
            [
                'role' => Role::User->value,
                'content' => [
                    [
                        'type' => 'text',
                        'text' => 'Mit der Information, dass das Bild "' . $image->title . '" heißt, beschreibe bis ins kleinste Detail jede relevante Information aus diesem Bild. Füge keine Links ein. Schlussfolgerungen möchtest du nicht machen, sondern nur den Inhalt beschreiben.',
                    ],
                    [
                        'type' => 'image_url',
                        'image_url' => ['url' => 'data:' . $image->mimeType . ';base64,' . $image->encodedImage],
                    ],
                ],
            ],
        ];

        $body = [
            'model' => Version::gpt4o()->name,
            'temperature' => $settings->getChatbotTuning()->getTemperature(),
            'messages' => $messages,
        ];

        $response = $this->runtime->request('chat/completions', $body);

        $choices = $response['choices'];
        if (! isset($choices) || ! is_array($choices)) {
            return '';
        }

        $firstChoice = $choices[0];
        if (! isset($firstChoice['message']) || ! is_array($firstChoice['message'])) {
            return '';
        }

        $message = $firstChoice['message'];
        if (! isset($message['content']) || ! is_string($message['content'])) {
            return '';
        }

        return $message['content'];
    }
}
