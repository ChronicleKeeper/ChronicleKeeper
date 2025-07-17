<?php

declare(strict_types=1);

namespace ChronicleKeeper\Image\Application\Service\ImportExport;

use ChronicleKeeper\Settings\Application\Service\Importer\SingleImport;
use ChronicleKeeper\Settings\Application\Service\ImportSettings;
use Doctrine\DBAL\Connection;
use League\Flysystem\FileAttributes;
use League\Flysystem\Filesystem;
use Psr\Log\LoggerInterface;

use function assert;
use function count;
use function json_decode;
use function reset;

use const JSON_THROW_ON_ERROR;

final readonly class ImageEmbeddingsImporter implements SingleImport
{
    public function __construct(
        private Connection $connection,
        private LoggerInterface $logger,
    ) {
    }

    public function import(Filesystem $filesystem, ImportSettings $settings): void
    {
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

            // Delete existing vectors for this image before inserting new ones
            $this->connection->createQueryBuilder()
                ->delete('images_vectors')
                ->where('image_id = :imageId')
                ->setParameter('imageId', $imageId)
                ->executeStatement();

            foreach ($content['data'] as $row) {
                $this->connection->createQueryBuilder()
                    ->insert('images_vectors')
                    ->values([
                        'image_id' => ':imageId',
                        'embedding' => ':embedding',
                        'content' => ':content',
                        '"vectorContentHash"' => ':vectorContentHash',
                    ])
                    ->setParameters([
                        'imageId' => $row['image_id'],
                        'embedding' => $row['embedding'],
                        'content' => $row['content'],
                        'vectorContentHash' => $row['vectorContentHash'],
                    ])
                    ->executeStatement();
            }
        }
    }

    private function hasImageVectors(string $id): bool
    {
        $result = $this->connection->createQueryBuilder()
            ->select('image_id')
            ->from('images_vectors')
            ->where('image_id = :imageId')
            ->setParameter('imageId', $id)
            ->executeQuery()
            ->fetchOne();

        return $result !== false;
    }
}
