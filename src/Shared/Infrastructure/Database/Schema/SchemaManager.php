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
use function in_array;
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

    /** @param list<class-string<SchemaProvider>> $onlySchemaProviders */
    public function createSchema(array $onlySchemaProviders = []): void
    {
        try {
            $this->platform->beginTransaction();

            $providers = iterator_to_array($this->schemaProviders);
            usort($providers, static fn (
                SchemaProvider $a,
                SchemaProvider $b,
            ) => $a->getPriority() <=> $b->getPriority());

            foreach ($providers as $schemaProvider) {
                if ($onlySchemaProviders !== [] && ! in_array($schemaProvider::class, $onlySchemaProviders, true)) {
                    continue;
                }

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
        $tables = $this->platform->fetch('SELECT tablename FROM pg_tables WHERE schemaname = :schema', ['schema' => 'public']);

        return array_column($tables, 'tablename');
    }

    public function dropSchema(): void
    {
        try {
            $this->platform->beginTransaction();

            // Disable triggers temporarily
            $this->platform->executeRaw('SET session_replication_role = replica;');

            // Drop all objects in correct order
            $this->platform->executeRaw("
                DO $$
                DECLARE
                    _sql text;
                BEGIN
                    -- Drop Views
                    FOR _sql IN
                        SELECT 'DROP VIEW IF EXISTS ' || quote_ident(schemaname) || '.' || quote_ident(viewname) || ' CASCADE'
                        FROM pg_views WHERE schemaname = 'public'
                    LOOP
                        EXECUTE _sql;
                    END LOOP;

                    -- Drop Tables
                    FOR _sql IN
                        SELECT 'DROP TABLE IF EXISTS ' || quote_ident(schemaname) || '.' || quote_ident(tablename) || ' CASCADE'
                        FROM pg_tables WHERE schemaname = 'public'
                    LOOP
                        EXECUTE _sql;
                    END LOOP;

                    -- Drop Types
                    FOR _sql IN
                        SELECT 'DROP TYPE IF EXISTS ' || quote_ident(t.typname) || ' CASCADE'
                        FROM pg_type t JOIN pg_namespace n ON (t.typnamespace = n.oid)
                        WHERE n.nspname = 'public' AND t.typtype = 'c'
                    LOOP
                        EXECUTE _sql;
                    END LOOP;
                END $$;
            ");

            // Reset triggers
            $this->platform->executeRaw('SET session_replication_role = DEFAULT;');

            $this->platform->commit();
        } catch (Throwable $e) {
            $this->platform->rollback();

            throw $e;
        }
    }
}
