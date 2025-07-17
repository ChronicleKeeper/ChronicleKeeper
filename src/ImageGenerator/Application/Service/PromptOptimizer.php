<?php

declare(strict_types=1);

namespace ChronicleKeeper\ImageGenerator\Application\Service;

use ChronicleKeeper\Chat\Application\Service\SingleChatMessageExecution;
use ChronicleKeeper\Chat\Domain\Entity\Conversation;
use ChronicleKeeper\Chat\Domain\Entity\Message;
use ChronicleKeeper\Settings\Application\Service\SystemPromptRegistry;
use ChronicleKeeper\Settings\Domain\ValueObject\SystemPrompt\Purpose;

use function array_key_last;

class PromptOptimizer
{
    public function __construct(
        private readonly SingleChatMessageExecution $chatMessageExecution,
        private readonly SystemPromptRegistry $systemPromptRegistry,
    ) {
    }

    public function optimize(string $originPrompt): string
    {
        // Aus dem Prompt des Nutzers einen Prompt für die Bildgenerierung auf Basis des Inhaltes der Bibliothek machen
        $conversation = Conversation::createEmpty();
        $messages     = $conversation->getMessages();

        // Get the System prompt from the registry
        $systemPrompt = $this->systemPromptRegistry->getDefaultForPurpose(Purpose::IMAGE_GENERATOR_OPTIMIZER);

        // Create Prompt that makes clear the user message should be rewritten to a Dall-E prompt
        $messages[] = Message::forSystem($systemPrompt->getContent());

        $this->chatMessageExecution->execute($originPrompt, $conversation);

        $messages = $conversation->getMessages()->getArrayCopy();

        return $messages[array_key_last($messages)]->getContent();
    }
}
