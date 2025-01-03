<?php

declare(strict_types=1);

namespace ChronicleKeeper\Shared\Infrastructure\Database\Schema;

abstract class DefaultSchemaProvider implements SchemaProvider
{
    public function getPriority(): int
    {
        return 100;
    }
}
