<?php

declare(strict_types=1);

namespace ChronicleKeeper\Shared\Infrastructure\Persistence\Filesystem\Exception;

use InvalidArgumentException;

use function sprintf;

class PathNotRegistered extends InvalidArgumentException
{
    public function __construct(string $type)
    {
        parent::__construct(sprintf(
            'Path for type "%s" is not registered.',
            $type,
        ));
    }
}
