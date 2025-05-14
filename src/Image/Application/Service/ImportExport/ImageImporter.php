<?php

declare(strict_types=1);

namespace ChronicleKeeper\Image\Application\Service\ImportExport;

use ChronicleKeeper\Settings\Application\Service\Importer\SingleImport;
use ChronicleKeeper\Settings\Application\Service\ImportSettings;
use Doctrine\DBAL\Connection;
use League\Flysystem\FileAttributes;
use League\Flysystem\Filesystem;
use Psr\Log\LoggerInterface;

use function array_key_exists;
use function assert;
use function json_decode;

use const JSON_THROW_ON_ERROR;

final readonly class ImageImporter implements SingleImport
{
    public function __construct(
        private Connection $connection,
        private LoggerInterface $logger,
    ) {
    }

    public function import(Filesystem $filesystem, ImportSettings $settings): void
    {
        foreach ($filesystem->listContents('library/images/') as $zippedFile) {
            assert($zippedFile instanceof FileAttributes);

            $this->importImage($filesystem, $zippedFile, $settings);
        }
    }

    private function importImage(Filesystem $filesystem, FileAttributes $file, ImportSettings $settings): void
    {
        $content = $filesystem->read($file->path());
        $content = json_decode($content, true, 512, JSON_THROW_ON_ERROR);

        if (array_key_exists('data', $content)) {
            // Workaround for Imports from versions < 0.7
            $content = $content['data'];
        }

        if ($settings->overwriteLibrary === false && $this->hasImage($content['id'])) {
            $this->logger->debug('Image already exists, skipping.', ['image_id' => $content['id']]);

            return;
        }

        $this->logger->debug('Importing image.', ['image_id' => $content['id']]);

        // Check if image exists to determine if insert or update is needed
        if ($this->hasImage($content['id'])) {
            // Update existing image
            $this->connection->createQueryBuilder()
                ->update('images')
                ->set('title', ':title')
                ->set('mime_type', ':mime_type')
                ->set('encoded_image', ':encoded_image')
                ->set('description', ':description')
                ->set('directory', ':directory')
                ->set('last_updated', ':last_updated')
                ->where('id = :id')
                ->setParameters([
                    'id' => $content['id'],
                    'title' => $content['title'],
                    'mime_type' => $content['mime_type'],
                    'encoded_image' => $content['encoded_image'],
                    'description' => $content['description'],
                    'directory' => $content['directory'],
                    'last_updated' => $content['last_updated'],
                ])
                ->executeStatement();
        } else {
            // Insert new image
            $this->connection->createQueryBuilder()
                ->insert('images')
                ->values([
                    'id' => ':id',
                    'title' => ':title',
                    'mime_type' => ':mime_type',
                    'encoded_image' => ':encoded_image',
                    'description' => ':description',
                    'directory' => ':directory',
                    'last_updated' => ':last_updated',
                ])
                ->setParameters([
                    'id' => $content['id'],
                    'title' => $content['title'],
                    'mime_type' => $content['mime_type'],
                    'encoded_image' => $content['encoded_image'],
                    'description' => $content['description'],
                    'directory' => $content['directory'],
                    'last_updated' => $content['last_updated'],
                ])
                ->executeStatement();
        }
    }

    private function hasImage(string $id): bool
    {
        $result = $this->connection->createQueryBuilder()
            ->select('id')
            ->from('images')
            ->where('id = :id')
            ->setParameter('id', $id)
            ->executeQuery()
            ->fetchOne();

        return $result !== false;
    }
}
