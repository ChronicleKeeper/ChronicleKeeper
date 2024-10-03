<?php

declare(strict_types=1);

namespace ChronicleKeeper\Shared\Infrastructure\Persistence\Filesystem\Exception;

use InvalidArgumentException;

use function sprintf;

class UnableToDeleteFile extends InvalidArgumentException
{
    public function __construct(string $path)
    {
        parent::__construct(sprintf(
            'Path "%s" is not deletable, please check permissions or existence of file.',
            $path,
        ));
    }
}
