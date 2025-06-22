<?php

declare(strict_types=1);

namespace ChronicleKeeper\ImageGenerator\Application\Service;

use ChronicleKeeper\Chat\Application\Service\SingleChatMessageExecution;
use ChronicleKeeper\Chat\Domain\Entity\Conversation;
use ChronicleKeeper\Chat\Domain\Entity\ExtendedMessage;
use ChronicleKeeper\Settings\Application\Service\SystemPromptRegistry;
use ChronicleKeeper\Settings\Domain\ValueObject\SystemPrompt\Purpose;
use PhpLlm\LlmChain\Platform\Message\AssistantMessage;
use PhpLlm\LlmChain\Platform\Message\Message;

use function array_key_last;
use function assert;

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
        $messages[] = new ExtendedMessage(Message::forSystem($systemPrompt->getContent()));

        $this->chatMessageExecution->execute($originPrompt, $conversation);

        $messages        = $conversation->getMessages()->getLLMChainMessages()->getMessages();
        $optimizedPrompt = $messages[array_key_last($messages)];
        assert($optimizedPrompt instanceof AssistantMessage);

        return (string) $optimizedPrompt->content;
    }
}
