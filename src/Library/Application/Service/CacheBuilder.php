<?php

declare(strict_types=1);

namespace ChronicleKeeper\Library\Application\Service;

use ChronicleKeeper\Chat\Application\Query\FindConversationsByDirectoryParameters;
use ChronicleKeeper\Document\Application\Query\FindDocumentsByDirectory;
use ChronicleKeeper\Library\Domain\Entity\Directory as DirectoryEntity;
use ChronicleKeeper\Library\Domain\ValueObject\DirectoryCache\Directory;
use ChronicleKeeper\Library\Domain\ValueObject\DirectoryCache\Element;
use ChronicleKeeper\Library\Infrastructure\Repository\FilesystemDirectoryRepository;
use ChronicleKeeper\Library\Infrastructure\Repository\FilesystemImageRepository;
use ChronicleKeeper\Shared\Application\Query\QueryService;

use function strcasecmp;
use function usort;

class CacheBuilder
{
    public function __construct(
        private readonly FilesystemImageRepository $imageRepository,
        private readonly FilesystemDirectoryRepository $directoryRepository,
        private readonly QueryService $queryService,
    ) {
    }

    public function build(DirectoryEntity $directory): Directory
    {
        return $this->buildForEntity($directory);
    }

    private function buildForEntity(DirectoryEntity $directory): Directory
    {
        $cacheDirectory = Directory::fromEntity($directory);

        // Add directories to the cache
        $childDirectories = $this->directoryRepository->findByParent($directory);
        foreach ($childDirectories as $childDirectory) {
            $cacheDirectory->directories[] = Directory::fromEntity($childDirectory);
        }

        unset($childDirectories);

        // Sort directories by their title
        usort(
            $cacheDirectory->directories,
            static fn (Directory $left, Directory $right) => strcasecmp($left->title, $right->title),
        );

        // Add documents to the cache
        $documents = $this->queryService->query(new FindDocumentsByDirectory($directory->getId()));
        foreach ($documents as $document) {
            $cacheDirectory->elements[] = Element::fromDocumentEntity($document);
        }

        unset($documents);

        // Add images to the cache
        $images = $this->imageRepository->findByDirectory($directory);
        foreach ($images as $image) {
            $cacheDirectory->elements[] = Element::fromImageEntity($image);
        }

        unset($images);

        // Add conversations to the cache
        $conversations = $this->queryService->query(new FindConversationsByDirectoryParameters($directory));
        foreach ($conversations as $conversation) {
            $cacheDirectory->elements[] = Element::fromConversationEntity($conversation);
        }

        unset($conversations);

        // Sort the elements that were added to the directory by slug
        usort(
            $cacheDirectory->elements,
            static fn (Element $left, Element $right) => strcasecmp($left->slug, $right->slug),
        );

        return $cacheDirectory;
    }
}
