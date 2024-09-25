<?php

declare(strict_types=1);

namespace ChronicleKeeper\Settings\Domain\ValueObject\Settings;

/**
 * @phpstan-type ChatbotGeneralSettings = array{
 *     max_document_responses: int,
 *     max_image_responses: int,
 *     chatbot_name: string,
 *     chatter_name: string,
 *     show_referenced_documents: bool,
 *     show_referenced_images: bool
 * }
 */
readonly class ChatbotGeneral
{
    public function __construct(
        private int $maxDocumentResponses = 4,
        private int $maxImageResponses = 2,
        private string $chatbotName = 'Chronicle Keeper',
        private string $chatterName = 'Der Unbekannte',
        private bool $showReferencedDocuments = true,
        private bool $showReferencedImages = true,
    ) {
    }

    /** @param ChatbotGeneralSettings $settings */
    public static function fromArray(array $settings): ChatbotGeneral
    {
        return new ChatbotGeneral(
            $settings['max_document_responses'],
            $settings['max_image_responses'],
            $settings['chatbot_name'],
            $settings['chatter_name'],
            $settings['show_referenced_documents'],
            $settings['show_referenced_images'],
        );
    }

    /** @return ChatbotGeneralSettings */
    public function toArray(): array
    {
        return [
            'max_document_responses' => $this->maxDocumentResponses,
            'max_image_responses' => $this->maxImageResponses,
            'chatbot_name' => $this->chatbotName,
            'chatter_name' => $this->chatterName,
            'show_referenced_documents' => $this->showReferencedDocuments,
            'show_referenced_images' => $this->showReferencedImages,
        ];
    }

    public function getMaxDocumentResponses(): int
    {
        return $this->maxDocumentResponses;
    }

    public function getMaxImageResponses(): int
    {
        return $this->maxImageResponses;
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

    public function showReferencedImages(): bool
    {
        return $this->showReferencedImages;
    }
}
