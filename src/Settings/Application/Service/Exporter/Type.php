<?php

declare(strict_types=1);

namespace ChronicleKeeper\Settings\Application\Service\Exporter;

enum Type: string
{
    // File based types
    case FAVORITES = 'favorites';
    case SETTINGS  = 'settings';
    case PROMPTS   = 'prompts';

    // From Database
    case DIRECTORY          = 'directory';
    case CONVERSATION       = 'conversation';
    case DOCUMENT           = 'document';
    case DOCUMENT_EMBEDDING = 'document_embedding';
    case IMAGE              = 'image';
    case IMAGE_EMBEDDING    = 'image_embedding';
    case WORLD_ITEM         = 'world_item';
}
