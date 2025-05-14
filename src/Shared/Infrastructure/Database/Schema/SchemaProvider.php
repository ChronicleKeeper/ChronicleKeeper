<?php

declare(strict_types=1);

namespace ChronicleKeeper\Shared\Infrastructure\Database\Schema;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('chronicle_keeper.schema_provider')]
interface SchemaProvider
{
    /**
     * Creates database schema
     *
     * @param Connection $connection Doctrine DBAL connection
     *
     * @throws Exception When a database error occurs.
     */
    public function createSchema(Connection $connection): void;

    /**
     * Returns the priority of this schema provider
     * Lower numbers = higher priority (executed first)
     */
    public function getPriority(): int;
}
