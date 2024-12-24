<?php

declare(strict_types=1);

namespace ChronicleKeeper\Library\Application\Service;

use ChronicleKeeper\Library\Domain\Entity\Directory as DirectoryEntity;
use ChronicleKeeper\Library\Domain\ValueObject\DirectoryCache\Directory as DirectoryCache;
use ChronicleKeeper\Shared\Infrastructure\Persistence\Filesystem\Contracts\FileAccess;
use Symfony\Component\Serializer\SerializerInterface;

class CacheReader
{
    public function __construct(
        private readonly FileAccess $fileAccess,
        private readonly CacheBuilder $cacheBuilder,
        private readonly SerializerInterface $serializer,
    ) {
    }

    public function refresh(DirectoryEntity $directory): DirectoryCache
    {
        $filename = $directory->id . '.json';

        $cache = $this->cacheBuilder->build($directory);
        $this->fileAccess->write(
            'library.directories.cache',
            $filename,
            $this->serializer->serialize($cache, 'json'),
        );

        return $cache;
    }

    public function read(DirectoryEntity $directory): DirectoryCache
    {
        $filename    = $directory->id . '.json';
        $cacheExists = $this->fileAccess->exists('library.directories.cache', $filename);
        if ($cacheExists) {
            return $this->serializer->deserialize(
                $this->fileAccess->read('library.directories.cache', $filename),
                DirectoryCache::class,
                'json',
            );
        }

        return $this->refresh($directory);
    }
}
