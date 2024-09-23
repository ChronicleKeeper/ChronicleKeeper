<?php

declare(strict_types=1);

namespace DZunke\NovDoc\Settings\Domain\ValueObject\Settings;

use DZunke\NovDoc\Chat\SystemPrompt;

/**
 * @phpstan-type ChatbotSystemPromptSettings = array{
 *     system_prompt: string
 * }
 */
readonly class ChatbotSystemPrompt
{
    public function __construct(
        private string $systemPrompt = SystemPrompt::GAMEMASTER,
    ) {
    }

    /** @param ChatbotSystemPromptSettings $settings */
    public static function fromArray(array $settings): ChatbotSystemPrompt
    {
        return new ChatbotSystemPrompt($settings['system_prompt']);
    }

    /** @return ChatbotSystemPromptSettings */
    public function toArray(): array
    {
        return ['system_prompt' => $this->systemPrompt];
    }

    public function getSystemPrompt(): string
    {
        return $this->systemPrompt;
    }
}
