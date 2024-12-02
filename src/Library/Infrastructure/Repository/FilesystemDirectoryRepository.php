<?php

declare(strict_types=1);

namespace ChronicleKeeper\Library\Infrastructure\Repository;

use ChronicleKeeper\Chat\Application\Command\DeleteConversation;
use ChronicleKeeper\Chat\Application\Query\FindConversationsByDirectoryParameters;
use ChronicleKeeper\Library\Domain\Entity\Directory;
use ChronicleKeeper\Library\Domain\RootDirectory;
use ChronicleKeeper\Shared\Application\Query\QueryService;
use ChronicleKeeper\Shared\Infrastructure\Persistence\Filesystem\Contracts\FileAccess;
use ChronicleKeeper\Shared\Infrastructure\Persistence\Filesystem\Exception\UnableToReadFile;
use ChronicleKeeper\Shared\Infrastructure\Persistence\Filesystem\PathRegistry;
use Psr\Log\LoggerInterface;
use RuntimeException;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Throwable;

use function array_filter;
use function array_merge;
use function array_values;
use function json_encode;
use function strcasecmp;
use function usort;

use const JSON_PRETTY_PRINT;
use const JSON_THROW_ON_ERROR;

class FilesystemDirectoryRepository
{
    private const string STORAGE_NAME = 'library.directories';

    public function __construct(
        private readonly LoggerInterface $logger,
        private readonly SerializerInterface $serializer,
        private readonly FileAccess $fileAccess,
        private readonly FilesystemImageRepository $imageRepository,
        private readonly FilesystemDocumentRepository $documentRepository,
        private readonly PathRegistry $pathRegistry,
        private readonly QueryService $queryService,
        private readonly MessageBusInterface $bus,
    ) {
    }

    public function store(Directory $directory): void
    {
        $filename = $directory->id . '.json';
        $content  = json_encode(
            $directory->toArray(),
            JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT,
        );

        $this->fileAccess->write(self::STORAGE_NAME, $filename, $content);
    }

    public function remove(Directory $directory): void
    {
        $filename = $directory->id . '.json';

        $this->thePurge($directory);
        $this->fileAccess->delete(self::STORAGE_NAME, $filename);
    }

    private function thePurge(Directory $sourceDirectory): void
    {
        foreach ($this->findByParent($sourceDirectory) as $directory) {
            $this->thePurge($directory);
            $this->remove($directory);
        }

        foreach ($this->documentRepository->findByDirectory($sourceDirectory) as $document) {
            $this->documentRepository->remove($document);
        }

        foreach ($this->imageRepository->findByDirectory($sourceDirectory) as $image) {
            $this->imageRepository->remove($image);
        }

        foreach ($this->queryService->query(new FindConversationsByDirectoryParameters($sourceDirectory)) as $conversation) {
            $this->bus->dispatch(new DeleteConversation($conversation->id));
        }
    }

    /** @return list<Directory> */
    public function findAll(): array
    {
        $finder = (new Finder())
            ->ignoreDotFiles(true)
            ->in($this->pathRegistry->get(self::STORAGE_NAME))
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
    public function fetchFlattenedTree(Directory $root): array
    {
        $flattenedTree = [$root];

        $children = $this->findByParent($root);
        foreach ($children as $child) {
            $flattenedTree = array_merge(
                $flattenedTree,
                $this->fetchFlattenedTree($child),
            );
        }

        return $flattenedTree;
    }

    /** @return list<Directory> */
    public function findByParent(Directory $parent): array
    {
        $directories = $this->findAll();

        return array_values(array_filter(
            $directories,
            static fn (Directory $directory) => $directory->parent?->id === $parent->id,
        ));
    }

    public function findById(string $id): Directory|null
    {
        if ($id === RootDirectory::ID) {
            return RootDirectory::get();
        }

        try {
            $json = $this->fileAccess->read(self::STORAGE_NAME, $id . '.json');
        } catch (UnableToReadFile) {
            return null;
        }

        try {
            return $this->convertJsonToDirectory($json);
        } catch (Throwable $e) {
            $this->logger->error($e, ['json' => $json]);

            throw $e;
        }
    }

    private function convertJsonToDirectory(string $json): Directory
    {
        return $this->serializer->deserialize($json, Directory::class, 'json');
    }
}
