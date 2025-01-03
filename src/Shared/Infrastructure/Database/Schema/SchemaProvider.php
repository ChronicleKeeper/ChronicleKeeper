<?php

declare(strict_types=1);

namespace ChronicleKeeper\Shared\Infrastructure\Database\Schema;

use ChronicleKeeper\Shared\Infrastructure\Database\DatabasePlatform;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('chronicle_keeper.schema_provider')]
interface SchemaProvider
{
    public function createSchema(DatabasePlatform $platform): void;

    public function getPriority(): int;
}
