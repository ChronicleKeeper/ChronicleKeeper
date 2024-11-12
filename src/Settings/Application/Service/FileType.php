<?php

declare(strict_types=1);

namespace ChronicleKeeper\Settings\Application\Service;

enum FileType: string
{
    case LIBRARY_DIRECTORY = 'directory';
    case LIBRARY_IMAGE     = 'image';
    case LIBRARY_DOCUMENT  = 'document';

    case CHAT_CONVERSATION = 'conversation';

    case VECTOR_STORAGE_DOCUMENT = 'vector_document';
    case VECTOR_STORAGE_IMAGE    = 'vector_image';

    case SETTINGS  = 'settings';
    case FAVORITES = 'favorites';
    case DOTENV    = 'dotenv';
    case VERSION   = 'version';
}
