<?php

declare(strict_types=1);

namespace ChronicleKeeper\Shared\Infrastructure\Database\Schema;

use ChronicleKeeper\Shared\Infrastructure\Database\DatabasePlatform;
use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;
use Symfony\Component\DependencyInjection\Attribute\Lazy;
use Throwable;

use function iterator_to_array;
use function usort;

#[Lazy]
class SchemaManager
{
    /**
     * @param iterable<SchemaProvider> $schemaProviders
     * @param iterable<DataProvider>   $dataProviders
     */
    public function __construct(
        private readonly DatabasePlatform $platform,
        #[AutowireIterator('chronicle_keeper.schema_provider')]
        private readonly iterable $schemaProviders,
        #[AutowireIterator('chronicle_keeper.data_provider')]
        private readonly iterable $dataProviders,
    ) {
    }

    public function createSchema(): void
    {
        try {
            $this->platform->beginTransaction();

            $providers = iterator_to_array($this->schemaProviders);
            usort($providers, static fn (SchemaProvider $a, SchemaProvider $b) => $a->getPriority() <=> $b->getPriority());

            foreach ($providers as $schemaProvider) {
                $schemaProvider->createSchema($this->platform);
            }

            $providers = iterator_to_array($this->dataProviders);
            usort($providers, static fn (DataProvider $a, DataProvider $b) => $a->getPriority() <=> $b->getPriority());

            foreach ($providers as $dataProvider) {
                $dataProvider->loadData($this->platform);
            }

            $this->platform->commit();
        } catch (Throwable $e) {
            $this->platform->rollback();

            throw $e;
        }
    }

    public function dropSchema(): void
    {
        try {
            $this->platform->beginTransaction();

            $tables = $this->platform->fetch("SELECT name FROM sqlite_master WHERE type='table'");
            foreach ($tables as $table) {
                $this->platform->query('DROP TABLE IF EXISTS ' . $table['name']);
            }

            $this->platform->commit();
        } catch (Throwable $e) {
            $this->platform->rollback();

            throw $e;
        }
    }
}