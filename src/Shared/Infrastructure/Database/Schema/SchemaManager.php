<?php

declare(strict_types=1);

namespace ChronicleKeeper\Shared\Infrastructure\Database\Schema;

use ChronicleKeeper\Shared\Infrastructure\Database\DatabasePlatform;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;
use Symfony\Component\DependencyInjection\Attribute\Lazy;
use Throwable;

use function array_column;
use function array_filter;
use function array_values;
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
        private readonly LoggerInterface $logger = new NullLogger(),
    ) {
    }

    public function createSchema(): void
    {
        try {
            $this->platform->beginTransaction();

            $providers = iterator_to_array($this->schemaProviders);
            usort($providers, static fn (
                SchemaProvider $a,
                SchemaProvider $b,
            ) => $a->getPriority() <=> $b->getPriority());

            foreach ($providers as $schemaProvider) {
                $this->logger->debug('Creating schema with provider ' . $schemaProvider::class);
                $schemaProvider->createSchema($this->platform);
            }

            $providers = iterator_to_array($this->dataProviders);
            usort($providers, static fn (DataProvider $a, DataProvider $b) => $a->getPriority() <=> $b->getPriority());

            foreach ($providers as $dataProvider) {
                $this->logger->debug('Loading data with provider ' . $dataProvider::class);
                $dataProvider->loadData($this->platform);
            }

            $this->platform->commit();
        } catch (Throwable $e) {
            $this->platform->rollback();

            throw $e;
        }
    }

    /** @return array<int, string> */
    public function getTables(): array
    {
        $tables = array_column(
            $this->platform->fetch("SELECT name FROM sqlite_master WHERE type='table'"),
            'name',
        );

        return array_values(array_filter(
            $tables,
            static fn (string $table) => $table !== 'sqlite_sequence',
        ));
    }

    public function dropSchema(): void
    {
        try {
            $this->platform->beginTransaction();

            $tables = $this->getTables();
            foreach ($tables as $table) {
                $this->platform->executeRaw('DROP TABLE IF EXISTS ' . $table);
            }

            $this->platform->commit();
        } catch (Throwable $e) {
            $this->platform->rollback();

            throw $e;
        }
    }
}
