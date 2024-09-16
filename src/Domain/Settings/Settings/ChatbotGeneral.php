<?php

declare(strict_types=1);

namespace DZunke\NovDoc\Domain\Settings\Settings;

/**
 * @phpstan-type ChatbotGeneralSettings = array{
 *     max_document_responses: int,
 *     chatbot_name: string,
 *     chatter_name: string,
 *     show_referenced_documents: bool
 * }
 */
readonly class ChatbotGeneral
{
    public function __construct(
        private int $maxDocumentResponses = 4,
        private string $chatbotName = 'Rostbart',
        private string $chatterName = 'Elias',
        private bool $showReferencedDocuments = true,
    ) {
    }

    /** @param ChatbotGeneralSettings $settings */
    public static function fromArray(array $settings): ChatbotGeneral
    {
        return new ChatbotGeneral(
            $settings['max_document_responses'],
            $settings['chatbot_name'],
            $settings['chatter_name'],
            $settings['show_referenced_documents'],
        );
    }

    /** @return ChatbotGeneralSettings */
    public function toArray(): array
    {
        return [
            'max_document_responses' => $this->maxDocumentResponses,
            'chatbot_name' => $this->chatbotName,
            'chatter_name' => $this->chatterName,
            'show_referenced_documents' => $this->showReferencedDocuments,
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

    public function showReferencedDocuments(): bool
    {
        return $this->showReferencedDocuments;
    }
}
