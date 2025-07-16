<?php

declare(strict_types=1);

namespace ChronicleKeeper\Chat\Domain\ValueObject;

enum Role: string
{
    case SYSTEM    = 'system';
    case USER      = 'user';
    case ASSISTANT = 'assistant';
}
