<?php

declare(strict_types=1);

namespace ChronicleKeeper\Chat\Infrastructure\LLMChain;

use ChronicleKeeper\Chat\Domain\Entity\Message as ChatMessage;
use ChronicleKeeper\Chat\Domain\Entity\MessageBag as ChatMessageBag;
use ChronicleKeeper\Chat\Domain\ValueObject\Role;
use PhpLlm\LlmChain\Platform\Message\AssistantMessage;
use PhpLlm\LlmChain\Platform\Message\Content\Text;
use PhpLlm\LlmChain\Platform\Message\MessageBag as LLmMessageBag;
use PhpLlm\LlmChain\Platform\Message\SystemMessage;
use PhpLlm\LlmChain\Platform\Message\UserMessage;

class MessageBagConverter
{
    public function toLlmMessageBag(ChatMessageBag $chatMessageBag): LLmMessageBag
    {
        $llmMessages = [];
        foreach ($chatMessageBag as $message) {
            $role          = $message->getRole();
            $llmMessages[] = match ($role) {
                Role::SYSTEM => $this->toSystemMessage($message),
                Role::USER => $this->toUserMessage($message),
                Role::ASSISTANT => $this->toAssistantMessage($message),
            };
        }

        return new LLmMessageBag(...$llmMessages);
    }

    private function toSystemMessage(ChatMessage $message): SystemMessage
    {
        return new SystemMessage($message->getContent());
    }

    private function toUserMessage(ChatMessage $message): UserMessage
    {
        return new UserMessage(new Text($message->getContent()));
    }

    private function toAssistantMessage(ChatMessage $message): AssistantMessage
    {
        return new AssistantMessage($message->getContent());
    }
}
