<?php

declare(strict_types=1);

namespace ChronicleKeeper\Image\Application\Service\ImportExport;

use ChronicleKeeper\Settings\Application\Service\Importer\SingleImport;
use ChronicleKeeper\Settings\Application\Service\ImportSettings;
use ChronicleKeeper\Shared\Infrastructure\Database\DatabasePlatform;
use League\Flysystem\FileAttributes;
use League\Flysystem\Filesystem;
use Psr\Log\LoggerInterface;

use function assert;
use function count;
use function implode;
use function json_decode;
use function reset;

use const JSON_THROW_ON_ERROR;

final readonly class ImageEmbeddingsImporter implements SingleImport
{
    public function __construct(
        private DatabasePlatform $databasePlatform,
        private LoggerInterface $logger,
    ) {
    }

    public function import(Filesystem $filesystem, ImportSettings $settings): void
    {
        if (count($filesystem->listContents('vector/image/')->toArray()) > 0) {
            $this->classicImport($filesystem);

            return;
        }

        foreach ($filesystem->listContents('library/image_embeddings/') as $file) {
            assert($file instanceof FileAttributes);

            $fileContent = $filesystem->read($file->path());
            $content     = json_decode($fileContent, true, 512, JSON_THROW_ON_ERROR);

            if (count($content['data']) === 0) {
                // Vector Storage is empty, no need to store something
                $this->logger->debug('Vector storage is empty, skipping.', ['file' => $file->path()]);
                continue;
            }

            $imageId = reset($content['data'])['image_id'];
            if ($settings->overwriteLibrary === false && $this->hasImageVectors($imageId)) {
                // The image already has a vector storage, no need to overwrite
                $this->logger->debug('Image already has a vector storage, skipping.', ['image_id' => $imageId]);
                continue;
            }

            $this->databasePlatform->createQueryBuilder()->createDelete()
                ->from('images_vectors')
                ->where('image_id', '=', $imageId)
                ->execute();

            foreach ($content['data'] as $row) {
                $this->databasePlatform->createQueryBuilder()->createInsert()
                    ->insert('images_vectors')
                    ->values([
                        'image_id' => $row['image_id'],
                        'embedding' => $row['embedding'],
                        'content' => $row['content'],
                        'vectorContentHash' => $row['vectorContentHash'],
                    ])
                    ->execute();
            }
        }
    }

    private function classicImport(Filesystem $filesystem): void
    {
        $libraryDirectoryPath = 'vector/image/';
        foreach ($filesystem->listContents($libraryDirectoryPath) as $zippedFile) {
            assert($zippedFile instanceof FileAttributes);

            $fileContent = $filesystem->read($zippedFile->path());

            /** @var array{id: string, imageId: string, content: string, vectorContentHash: string, vector: list<float>} $content */
            $content = json_decode($fileContent, true, 512, JSON_THROW_ON_ERROR);

            $this->databasePlatform->createQueryBuilder()->createDelete()
                ->from('images_vectors')
                ->where('image_id', '=', $content['imageId'])
                ->execute();

            $this->databasePlatform->createQueryBuilder()->createInsert()
                ->insert('images_vectors')
                ->values([
                    'image_id' => $content['imageId'],
                    'embedding' => '[' . implode(',', $content['vector']) . ']',
                    'content' => $content['content'],
                    'vectorContentHash' => $content['vectorContentHash'],
                ])
                ->execute();

            $this->logger->debug('Image vector storage imported.', ['vector_id' => $content['id']]);
        }
    }

    private function hasImageVectors(string $id): bool
    {
        return $this->databasePlatform->createQueryBuilder()->createSelect()
            ->select('id')
            ->from('images_vectors')
            ->where('id', '=', $id)
            ->fetchOneOrNull() !== null;
    }
}
