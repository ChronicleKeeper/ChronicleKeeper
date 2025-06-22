<?php

declare(strict_types=1);

namespace ChronicleKeeper\Shared\Infrastructure\Database\Schema;

use Doctrine\DBAL\Connection;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('chronicle_keeper.data_provider')]
interface DataProvider
{
    public function loadData(Connection $connection): void;

    public function getPriority(): int;
}
