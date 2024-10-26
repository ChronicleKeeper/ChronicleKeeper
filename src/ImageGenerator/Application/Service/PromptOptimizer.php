<?php

declare(strict_types=1);

namespace ChronicleKeeper\ImageGenerator\Application\Service;

use ChronicleKeeper\Chat\Application\Service\ChatMessageExecution;
use ChronicleKeeper\Chat\Domain\Entity\Conversation;
use ChronicleKeeper\Chat\Domain\Entity\ExtendedMessage;
use PhpLlm\LlmChain\Message\AssistantMessage;
use PhpLlm\LlmChain\Message\Message;

use function array_key_last;
use function assert;

class PromptOptimizer
{
    private const string DALL_E_PROMT_GENERATOR_PROMT = <<<'TEXT'
    You are an assistant to the user who tries to find out a perfect system prompt to hand over to Dall E Image generation.
    You will split the users message into persons, locations and other helpful pieces where more detailed visual descriptions
    to generate a perfect image that fits the users request.

    For each split you will call the function "library_documents" and try to find information about the style and visualization of persons and locations.
    For each split you will call the function "library_images" and try to find information about the style and visualization of persons and locations.

    You will enhance the given users prompt with the function responses in a way it describes the wanted situation as detailed as possible.

    Within your response you will:
    - Give a detailed description for characters that is as detailed as possible.
    - Give a detailed description for locations that is as detailed as possible.
    - Give a detailed description of the situation.
    - You will utilize every piece of function response that is useful for detailed descriptions.
    - You will strip all personal information that could not be useful for the image generation, like names or other personal information.
    - You will strip all information that is not useful for the image generation, like greetings or names of locations.

    Your answers will be optimized to a utilization of the image generation model dall-e-3.
    Your answer will not contain an explanation why your answer is like it is.
    Your answer will be formatted in markdown.
    Your answer will be in the language of the users request.
    Your answer will not contain any images.
    TEXT;

    public function __construct(
        private readonly ChatMessageExecution $chatMessageExecution,
    ) {
    }

    public function optimize(string $originPrompt): string
    {
        // Aus dem Prompt des Nutzers einen Prompt für die Bildgenerierung auf Basis des Inhaltes der Bibliothek machen
        $conversation = Conversation::createEmpty();
        // Create Prompt that makes clear the user message should be rewritten to a Dall-E prompt
        $conversation->messages[] = new ExtendedMessage(Message::forSystem(self::DALL_E_PROMT_GENERATOR_PROMT));

        $this->chatMessageExecution->execute($originPrompt, $conversation);

        $messages        = $conversation->messages->getLLMChainMessages()->getArrayCopy();
        $optimizedPrompt = $messages[array_key_last($messages)];
        assert($optimizedPrompt instanceof AssistantMessage);

        return (string) $optimizedPrompt->content;
    }
}
