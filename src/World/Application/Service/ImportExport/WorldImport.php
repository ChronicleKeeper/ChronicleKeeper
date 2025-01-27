<?php

declare(strict_types=1);

namespace ChronicleKeeper\World\Application\Service\ImportExport;

use ChronicleKeeper\Settings\Application\Service\Importer\SingleImport;
use ChronicleKeeper\Settings\Application\Service\ImportSettings;
use ChronicleKeeper\Shared\Infrastructure\Database\DatabasePlatform;
use League\Flysystem\FileAttributes;
use League\Flysystem\Filesystem;
use Psr\Log\LoggerInterface;

use function array_key_exists;
use function assert;
use function is_string;
use function json_decode;

use const JSON_THROW_ON_ERROR;

final readonly class WorldImport implements SingleImport
{
    public function __construct(
        private DatabasePlatform $databasePlatform,
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

        if (
            $settings->overwriteLibrary === false
            && $this->databasePlatform->hasRows('world_items', ['id' => $content['id']])
        ) {
            $this->logger->debug('World item already exists, skipping.', ['item_id' => $content['id']]);

            return;
        }

        $this->databasePlatform->insertOrUpdate(
            'world_items',
            [
                'id' => $content['id'],
                'type' => $content['type'],
                'name' => $content['name'],
                'short_description' => $content['shortDescription'],
            ],
        );

        foreach ($content['mediaReferences'] as $mediaReference) {
            assert(array_key_exists('type', $mediaReference));

            $this->importItemMediaReference($content['id'], $mediaReference);
        }

        foreach ($content['relations'] as $relation) {
            if ($this->relationExists($content['id'], $relation['toItem'], $relation['relationType'])) {
                continue;
            }

            $this->databasePlatform->insertOrUpdate(
                'world_item_relations',
                [
                    'source_world_item_id' => $content['id'],
                    'target_world_item_id' => $relation['toItem'],
                    'relation_type' => $relation['relationType'],
                ],
            );
        }
    }

    private function relationExists(string $fromItem, string $toItem, string $relationType): bool
    {
        if (
            $this->databasePlatform->hasRows('world_item_relations', [
                'source_world_item_id' => $fromItem,
                'target_world_item_id' => $toItem,
                'relation_type' => $relationType,
            ])
        ) {
            return true;
        }

        return $this->databasePlatform->hasRows('world_item_relations', [
            'source_world_item_id' => $toItem,
            'target_world_item_id' => $fromItem,
            'relation_type' => $relationType,
        ]);
    }

    /** @param array<string, mixed> $mediaReference */
    private function importItemMediaReference(string $itemId, array $mediaReference): void
    {
        assert(array_key_exists('type', $mediaReference) && is_string($mediaReference['type']));

        if ($mediaReference['type'] === 'document') {
            $this->databasePlatform->insertOrUpdate(
                'world_item_documents',
                $params = ['world_item_id' => $itemId, 'document_id' => $mediaReference['document_id']],
            );

            $this->logger->debug('Imported document media reference.', ['item_id' => $itemId] + $params);

            return;
        }

        if ($mediaReference['type'] === 'image') {
            $this->databasePlatform->insertOrUpdate(
                'world_item_images',
                $params = ['world_item_id' => $itemId, 'image_id' => $mediaReference['image_id']],
            );

            $this->logger->debug('Imported image media reference.', ['world_item_id' => $itemId] + $params);

            return;
        }

        if ($mediaReference['type'] !== 'conversation') {
            return;
        }

        $this->databasePlatform->insertOrUpdate(
            'world_item_conversations',
            $params = ['world_item_id' => $itemId, 'conversation_id' => $mediaReference['conversation_id']],
        );

        $this->logger->debug('Imported conversation media reference.', ['world_item_id' => $itemId] + $params);
    }
}
