<?php

declare(strict_types=1);

namespace ChronicleKeeper\Shared\Infrastructure\Database\Converter;

use InvalidArgumentException;

use function sprintf;

final class UnsupportedDataClass extends InvalidArgumentException
{
    public static function withClass(string $class): self
    {
        return new self(sprintf('Unsupported class "%s", maybe you need to add a row converter?', $class));
    }
}
