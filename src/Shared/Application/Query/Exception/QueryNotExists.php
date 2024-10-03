<?php

declare(strict_types=1);

namespace ChronicleKeeper\Shared\Application\Query\Exception;

use InvalidArgumentException;

use function sprintf;

final class QueryNotExists extends InvalidArgumentException
{
    /** @param class-string $queryClass */
    public function __construct(string $queryClass)
    {
        parent::__construct(sprintf(
            'The query class "%s" does not exists.',
            $queryClass,
        ));
    }
}
