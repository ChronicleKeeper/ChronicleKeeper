<?php

declare(strict_types=1);

namespace ChronicleKeeper\World\Application\Service\ImportExport;

use ChronicleKeeper\Settings\Application\Service\Importer\SingleImport;
use ChronicleKeeper\Settings\Application\Service\ImportSettings;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;
use League\Flysystem\FileAttributes;
use League\Flysystem\Filesystem;
use Psr\Log\LoggerInterface;

use function array_key_exists;
use function assert;
use function is_string;
use function json_decode;
use function str_replace;

use const JSON_THROW_ON_ERROR;

final readonly class WorldImport implements SingleImport
{
    public function __construct(
        private Connection $connection,
        private LoggerInterface $logger,
    ) {
    }

    public function import(Filesystem $filesystem, ImportSettings $settings): void
    {
        foreach ($filesystem->listContents('world/') as $file) {
            assert($file instanceof FileAttributes);

            $this->importItem($filesystem, $file, $settings);
        }
    }

    private function importItem(Filesystem $filesystem, FileAttributes $file, ImportSettings $settings): void
    {
        $content = $filesystem->read($file->path());
        $content = json_decode($content, true, 512, JSON_THROW_ON_ERROR);
        $content = $content['data'];

        if ($settings->overwriteLibrary === false && $this->hasWorldItem($content['id'])) {
            $this->logger->debug('World item already exists, skipping.', ['item_id' => $content['id']]);

            return;
        }

        // Upsert with explicit check
        if ($this->hasWorldItem($content['id'])) {
            $this->connection->createQueryBuilder()
                ->update('world_items')
                ->set('type', ':type')
                ->set('name', ':name')
                ->set('short_description', ':short_description')
                ->where('id = :id')
                ->setParameters([
                    'id' => $content['id'],
                    'type' => $content['type'],
                    'name' => $content['name'],
                    'short_description' => $content['shortDescription'],
                ])
                ->executeStatement();
        } else {
            $this->connection->createQueryBuilder()
                ->insert('world_items')
                ->values([
                    'id' => ':id',
                    'type' => ':type',
                    'name' => ':name',
                    'short_description' => ':short_description',
                ])
                ->setParameters([
                    'id' => $content['id'],
                    'type' => $content['type'],
                    'name' => $content['name'],
                    'short_description' => $content['shortDescription'],
                ])
                ->executeStatement();
        }

        foreach ($content['mediaReferences'] as $mediaReference) {
            assert(array_key_exists('type', $mediaReference));

            $this->importItemMediaReference($content['id'], $mediaReference);
        }

        foreach ($content['relations'] as $relation) {
            if ($this->relationExists($content['id'], $relation['toItem'], $relation['relationType'])) {
                continue;
            }

            try {
                if ($this->hasRelation($content['id'], $relation['toItem'], $relation['relationType'])) {
                    $this->connection->createQueryBuilder()
                        ->update('world_item_relations')
                        ->set('relation_type', ':relation_type')
                        ->where('source_world_item_id = :source_id')
                        ->andWhere('target_world_item_id = :target_id')
                        ->setParameters([
                            'relation_type' => $relation['relationType'],
                            'source_id' => $content['id'],
                            'target_id' => $relation['toItem'],
                        ])
                        ->executeStatement();
                } else {
                    $this->connection->createQueryBuilder()
                        ->insert('world_item_relations')
                        ->values([
                            'source_world_item_id' => ':source_id',
                            'target_world_item_id' => ':target_id',
                            'relation_type' => ':relation_type',
                        ])
                        ->setParameters([
                            'source_id' => $content['id'],
                            'target_id' => $relation['toItem'],
                            'relation_type' => $relation['relationType'],
                        ])
                        ->executeStatement();
                }
            } catch (Exception) {
                /**
                 * Fine... can currently fail because maybe the target is not existing but as both sides
                 * are in the export the import will succeed with the other side.
                 */
            }
        }
    }

    private function relationExists(string $fromItem, string $toItem, string $relationType): bool
    {
        $fromItemToItem = (bool) $this->connection->createQueryBuilder()
            ->select('COUNT(*) as count')
            ->from('world_item_relations')
            ->where('source_world_item_id = :source_id')
            ->andWhere('target_world_item_id = :target_id')
            ->andWhere('relation_type = :relation_type')
            ->setParameters([
                'source_id' => $fromItem,
                'target_id' => $toItem,
                'relation_type' => $relationType,
            ])
            ->executeQuery()
            ->fetchOne();

        if ($fromItemToItem) {
            return true;
        }

        return (bool) $this->connection->createQueryBuilder()
            ->select('COUNT(*) as count')
            ->from('world_item_relations')
            ->where('source_world_item_id = :source_id')
            ->andWhere('target_world_item_id = :target_id')
            ->andWhere('relation_type = :relation_type')
            ->setParameters([
                'source_id' => $toItem,
                'target_id' => $fromItem,
                'relation_type' => $relationType,
            ])
            ->executeQuery()
            ->fetchOne();
    }

    private function hasRelation(string $fromItem, string $toItem, string $relationType): bool
    {
        $result = $this->connection->createQueryBuilder()
            ->select('source_world_item_id')
            ->from('world_item_relations')
            ->where('source_world_item_id = :source_id')
            ->andWhere('target_world_item_id = :target_id')
            ->andWhere('relation_type = :relation_type')
            ->setParameters([
                'source_id' => $fromItem,
                'target_id' => $toItem,
                'relation_type' => $relationType,
            ])
            ->executeQuery()
            ->fetchOne();

        return $result !== false;
    }

    /** @param array<string, mixed> $mediaReference */
    private function importItemMediaReference(string $itemId, array $mediaReference): void
    {
        assert(array_key_exists('type', $mediaReference) && is_string($mediaReference['type']));

        if ($mediaReference['type'] === 'document') {
            $this->upsertMediaReference(
                'world_item_documents',
                [
                    'world_item_id' => $itemId,
                    'document_id' => $mediaReference['document_id'],
                ],
                'Imported document media reference.',
            );

            return;
        }

        if ($mediaReference['type'] === 'image') {
            $this->upsertMediaReference(
                'world_item_images',
                [
                    'world_item_id' => $itemId,
                    'image_id' => $mediaReference['image_id'],
                ],
                'Imported image media reference.',
            );

            return;
        }

        if ($mediaReference['type'] !== 'conversation') {
            return;
        }

        $this->upsertMediaReference(
            'world_item_conversations',
            [
                'world_item_id' => $itemId,
                'conversation_id' => $mediaReference['conversation_id'],
            ],
            'Imported conversation media reference.',
        );
    }

    /** @param array<string, string> $params */
    private function upsertMediaReference(string $table, array $params, string $logMessage): void
    {
        // Check if reference exists
        $qb = $this->connection->createQueryBuilder()
            ->select('1')
            ->from($table);

        $paramPositions = [];
        foreach ($params as $key => $value) {
            $paramName = str_replace('_', '', $key);
            $qb->andWhere($key . ' = :' . $paramName);
            $paramPositions[$paramName] = $value;
        }

        $exists = $qb->setParameters($paramPositions)
            ->executeQuery()
            ->fetchOne();

        $namedParams = [];
        foreach ($params as $key => $value) {
            $namedParams[':' . $key] = $value;
        }

        if ($exists !== false) {
            // If exists, update
            $qb = $this->connection->createQueryBuilder()
                ->update($table);

            $whereAdded = false;
            foreach ($params as $key => $value) {
                if (! $whereAdded) {
                    $qb->where($key . ' = :' . $key);
                    $whereAdded = true;
                } else {
                    $qb->andWhere($key . ' = :' . $key);
                }

                $qb->setParameter($key, $value);
            }

            $qb->executeStatement();
        } else {
            // If not, insert
            $qb = $this->connection->createQueryBuilder()
                ->insert($table);

            $columnValues = [];
            foreach ($params as $key => $value) {
                $columnValues[$key] = ':' . $key;
                $qb->setParameter($key, $value);
            }

            $qb->values($columnValues)
                ->executeStatement();
        }

        $this->logger->debug($logMessage, $params);
    }

    private function hasWorldItem(string $id): bool
    {
        $result = $this->connection->createQueryBuilder()
            ->select('id')
            ->from('world_items')
            ->where('id = :id')
            ->setParameter('id', $id)
            ->executeQuery()
            ->fetchOne();

        return $result !== false;
    }
}
