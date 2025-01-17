<?php

declare(strict_types=1);

namespace ChronicleKeeper\Shared\Infrastructure\Database\Schema;

use ChronicleKeeper\Shared\Infrastructure\Database\DatabasePlatform;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('chronicle_keeper.data_provider')]
interface DataProvider
{
    public function loadData(DatabasePlatform $platform): void;

    public function getPriority(): int;
}
