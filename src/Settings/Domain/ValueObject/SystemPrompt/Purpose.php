<?php

declare(strict_types=1);

namespace ChronicleKeeper\Settings\Domain\ValueObject\SystemPrompt;

enum Purpose: string
{
    case CONVERSATION              = 'conversation';
    case IMAGE_UPLOAD              = 'image_upload';
    case DOCUMENT_OPTIMIZER        = 'document_optimizer';
    case IMAGE_GENERATOR_OPTIMIZER = 'image_generator_optimizer';
}
