<?php

declare(strict_types=1);

namespace ChronicleKeeper\ImageGenerator\Application\Service;

use ChronicleKeeper\Chat\Application\Service\ChatMessageExecution;
use ChronicleKeeper\Chat\Domain\Entity\Conversation;
use ChronicleKeeper\Chat\Domain\Entity\ExtendedMessage;
use ChronicleKeeper\ImageGenerator\Application\Service\OpenAIGenerator\ResponseImage;
use ChronicleKeeper\Shared\Infrastructure\Persistence\Filesystem\Contracts\FileAccess;
use PhpLlm\LlmChain\Message\Message;
use PhpLlm\LlmChain\OpenAI\Platform;
use Symfony\Component\Serializer\Encoder\JsonEncode;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\SerializerInterface;

use const JSON_PRETTY_PRINT;
use const JSON_UNESCAPED_UNICODE;

class OpenAIGenerator
{
    private const string MODEL_DALL_E_2 = 'dall-e-2';
    private const string MODEL_DALL_E_3 = 'dall-e-3';

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

    Your answers will be optimized to a utilization of the image generation model dall-e-3.
    Your answer will not contain an explanation of what you have done, so just the required prompt for generating the image.
    Your answer will be formatted in markdown.
    Your answer will be in the language of the users request.
    Your answer will not contain images but their description.
    TEXT;

    public function __construct(
        private readonly Platform $platform,
        private readonly ChatMessageExecution $chatMessageExecution,
        private readonly FileAccess $fileAccess,
        private readonly SerializerInterface $serializer,
        private readonly PromptOptimizer $promptOptimizer,
    ) {
    }

    public function generate(string $prompt): Conversation
    {
        $optimizedPrompt = $this->promptOptimizer->optimize($prompt);

        $body = [
            'prompt' => $optimizedPrompt,
            'model' => self::MODEL_DALL_E_3,
            'response_format' => 'b64_json',
        ];

        $response = $this->platform->request('images/generations', $body);

        $image = null;
        foreach ($response['data'] as $responseImage) {
            $image = new ResponseImage($optimizedPrompt, 'image/png', $responseImage['b64_json']);
            break;
        }

        return $this->getConversation();
    }

    public function getConversation(): Conversation
    {
        if (! $this->fileAccess->exists('temp', 'image_generation.json')) {
            $this->initializeImageGeneration();
        }

        return $this->serializer->deserialize(
            $this->fileAccess->read('temp', 'image_generation.json'),
            Conversation::class,
            JsonEncoder::FORMAT,
        );
    }

    private function initializeImageGeneration(): void
    {
        // Aus dem Prompt des Nutzers einen Prompt für die Bildgenerierung auf Basis des Inhaltes der Bibliothek machen
        $conversation = Conversation::createEmpty();
        // Create Prompt that makes clear the user message should be rewritten to a Dall-E prompt
        $conversation->messages[] = new ExtendedMessage(message: Message::forSystem(self::DALL_E_PROMT_GENERATOR_PROMT));

        $this->fileAccess->write(
            'temp',
            'image_generation.json',
            $this->serializer->serialize(
                $conversation,
                JsonEncoder::FORMAT,
                [JsonEncode::OPTIONS => JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT],
            ),
        );
    }

    private function storeConversation(Conversation $conversation): void
    {
        $this->fileAccess->write(
            'temp',
            'image_generation.json',
            $this->serializer->serialize(
                $conversation,
                JsonEncoder::FORMAT,
                [JsonEncode::OPTIONS => JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT],
            ),
        );
    }
}
