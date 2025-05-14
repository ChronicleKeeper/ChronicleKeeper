<?php

declare(strict_types=1);

namespace ChronicleKeeper\Shared\Infrastructure\Database\Schema;

use Doctrine\DBAL\Connection;
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
        private readonly Connection $connection,
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
            $this->connection->beginTransaction();

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
                $schemaProvider->createSchema($this->connection);
            }

            $providers = iterator_to_array($this->dataProviders);
            usort($providers, static fn (DataProvider $a, DataProvider $b) => $a->getPriority() <=> $b->getPriority());

            foreach ($providers as $dataProvider) {
                $this->logger->debug('Loading data with provider ' . $dataProvider::class);
                $dataProvider->loadData($this->connection);
            }

            $this->connection->commit();
        } catch (Throwable $e) {
            $this->connection->rollBack();

            throw $e;
        }
    }

    /** @return array<int, string> */
    public function getTables(): array
    {
        $queryBuilder = $this->connection->createQueryBuilder()
            ->select('tablename')
            ->from('pg_tables')
            ->where('schemaname = :schema')
            ->setParameter('schema', 'public');

        $tables = $queryBuilder->executeQuery()->fetchAllAssociative();

        return array_column($tables, 'tablename');
    }

    public function dropSchema(): void
    {
        try {
            $this->connection->beginTransaction();

            // Check if the schema exists before attempting to drop it
            $schemaExists = $this->connection->executeQuery("
            SELECT EXISTS (
                SELECT 1 FROM information_schema.schemata WHERE schema_name = 'public'
            )
        ")->fetchOne();

            if ($schemaExists !== false) {
                $this->logger->debug('Public schema exists, dropping it');
                $this->connection->executeStatement('
                DROP SCHEMA public CASCADE;
                CREATE SCHEMA public;
                GRANT ALL ON SCHEMA public TO PUBLIC;
            ');
            } else {
                $this->logger->debug('Public schema does not exist, creating it');
                $this->connection->executeStatement('
                CREATE SCHEMA IF NOT EXISTS public;
                GRANT ALL ON SCHEMA public TO PUBLIC;
            ');
            }

            $this->connection->commit();
        } catch (Throwable $e) {
            $this->connection->rollBack();
            $this->logger->error('Schema drop failed: ' . $e->getMessage());

            throw $e;
        }
    }
}
