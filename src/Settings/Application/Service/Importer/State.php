<?php

declare(strict_types=1);

namespace DZunke\NovDoc\Settings\Application\Service\Importer;

enum State: string
{
    case SUCCESS = 'success';
    case IGNORED = 'ignored';
    case ERROR   = 'error';
}
