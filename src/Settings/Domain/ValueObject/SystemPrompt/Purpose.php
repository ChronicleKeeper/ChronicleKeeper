<?php

declare(strict_types=1);

namespace ChronicleKeeper\Settings\Domain\ValueObject\SystemPrompt;

enum Purpose: string
{
    case CONVERSATION              = 'conversation';
    case IMAGE_UPLOAD              = 'image_upload';
    case DOCUMENT_OPTIMIZER        = 'document_optimizer';
    case IMAGE_GENERATOR_OPTIMIZER = 'image_generator_optimizer';

    public function getLabel(): string
    {
        return match ($this) {
            self::CONVERSATION => 'Gespräche',
            self::IMAGE_UPLOAD => 'Bilder-Upload',
            self::DOCUMENT_OPTIMIZER => 'Dokumenten-Optimierung',
            self::IMAGE_GENERATOR_OPTIMIZER => 'Mathildes Atelier',
        };
    }
}
