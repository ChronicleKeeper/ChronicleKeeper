<?php

declare(strict_types=1);

namespace ChronicleKeeper\Shared\Infrastructure\Persistence\Filesystem\Exception;

use InvalidArgumentException;

use function sprintf;

class UnableToWriteFile extends InvalidArgumentException
{
    public function __construct(string $path)
    {
        parent::__construct(sprintf(
            'Path "%s" is not writeable, please check permissions or existence of file.',
            $path,
        ));
    }
}
