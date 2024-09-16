<?php

declare(strict_types=1);

namespace DZunke\NovDoc\Domain\Settings\Settings;

/**
 * @phpstan-type ChatbotGeneralSettings = array{
 *     max_document_responses: int,
 *     chatbot_name: string,
 *     chatter_name: string
 * }
 */
readonly class ChatbotGeneral
{
    public function __construct(
        private int $maxDocumentResponses = 4,
        private string $chatbotName = 'Rostbart',
        private string $chatterName = 'Elias',
    ) {
    }

    /** @param ChatbotGeneralSettings $settings */
    public static function fromArray(array $settings): ChatbotGeneral
    {
        return new ChatbotGeneral(
            $settings['max_document_responses'],
            $settings['chatbot_name'],
            $settings['chatter_name'],
        );
    }

    /** @return ChatbotGeneralSettings */
    public function toArray(): array
    {
        return [
            'max_document_responses' => $this->maxDocumentResponses,
            'chatbot_name' => $this->chatbotName,
            'chatter_name' => $this->chatterName,
        ];
    }

    public function getMaxDocumentResponses(): int
    {
        return $this->maxDocumentResponses;
    }

    public function getChatbotName(): string
    {
        return $this->chatbotName;
    }

    public function getChatterName(): string
    {
        return $this->chatterName;
    }
}
