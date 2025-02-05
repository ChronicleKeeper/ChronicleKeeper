<?php

declare(strict_types=1);

namespace ChronicleKeeper\Shared\Infrastructure\Database;

interface ConnectionFactory
{
    public function create(): mixed;
}
