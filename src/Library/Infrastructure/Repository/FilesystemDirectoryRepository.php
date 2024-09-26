<?php

declare(strict_types=1);

namespace ChronicleKeeper\Library\Infrastructure\Repository;

use ChronicleKeeper\Library\Domain\Entity\Directory;
use ChronicleKeeper\Library\Domain\RootDirectory;
use Psr\Log\LoggerInterface;
use RuntimeException;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Serializer\SerializerInterface;

use function array_filter;
use function file_exists;
use function file_get_contents;
use function file_put_contents;
use function is_readable;
use function json_encode;
use function json_validate;
use function strcasecmp;
use function usort;

use const DIRECTORY_SEPARATOR;
use const JSON_PRETTY_PRINT;
use const JSON_THROW_ON_ERROR;

class FilesystemDirectoryRepository
{
    public function __construct(
        private readonly string $directoryStoragePath,
        private readonly LoggerInterface $logger,
        private readonly SerializerInterface $serializer,
    ) {
    }

    public function store(Directory $directory): void
    {
        $filename = $directory->id . '.json';
        $filepath = $this->directoryStoragePath . DIRECTORY_SEPARATOR . $filename;

        file_put_contents(
            $filepath,
            json_encode(
                $directory->toArray(),
                JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT,
            ),
        );
    }

    /** @return list<Directory> */
    public function findAll(): array
    {
        $finder = (new Finder())
            ->ignoreDotFiles(true)
            ->in($this->directoryStoragePath)
            ->files();

        $directories   = [];
        $directories[] = RootDirectory::get();
        foreach ($finder as $file) {
            try {
                $directories[] = $this->convertJsonToDirectory($file->getContents());
            } catch (RuntimeException $e) {
                $this->logger->error($e, ['file' => $file]);
            }
        }

        usort(
            $directories,
            static fn (Directory $left, Directory $right) => strcasecmp(
                $left->flattenHierarchyTitle(),
                $right->flattenHierarchyTitle(),
            ),
        );

        return $directories;
    }

    /** @return list<Directory> */
    public function findByParent(Directory $parent): array
    {
        $directories = $this->findAll();

        return array_filter(
            $directories,
            static fn (Directory $directory) => $directory->parent?->id === $parent->id,
        );
    }

    public function findById(string $id): Directory|null
    {
        if ($id === RootDirectory::ID) {
            return RootDirectory::get();
        }

        $json = $this->getContentOfFile($id . '.json');

        if ($json === null || ! json_validate($json)) {
            return null;
        }

        try {
            return $this->convertJsonToDirectory($json);
        } catch (RuntimeException $e) {
            $this->logger->error($e, ['json' => $json]);

            return null;
        }
    }

    private function convertJsonToDirectory(string $json): Directory
    {
        return $this->serializer->deserialize($json, Directory::class, 'json');
    }

    private function getContentOfFile(string $filename): string|null
    {
        $filepath = $this->directoryStoragePath . DIRECTORY_SEPARATOR . $filename;
        if (! file_exists($filepath) || ! is_readable($filepath)) {
            return null;
        }

        $directoryJson = file_get_contents($filepath);
        if ($directoryJson === false || ! json_validate($directoryJson)) {
            return null;
        }

        return $directoryJson;
    }
}
