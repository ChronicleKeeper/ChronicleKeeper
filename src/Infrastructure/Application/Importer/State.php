<?php

declare(strict_types=1);

namespace DZunke\NovDoc\Infrastructure\Application\Importer;

enum State: string
{
    case SUCCESS = 'success';
    case IGNORED = 'ignored';
    case ERROR   = 'error';
}
