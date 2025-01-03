<?php

declare(strict_types=1);

namespace ChronicleKeeper\Shared\Infrastructure\Database\Schema;

abstract class DefaultDataProvider implements DataProvider
{
    public function getPriority(): int
    {
        return 100;
    }
}
