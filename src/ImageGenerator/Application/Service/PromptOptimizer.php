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
