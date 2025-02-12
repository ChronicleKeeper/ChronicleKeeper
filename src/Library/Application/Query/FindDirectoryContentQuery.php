<?php

declare(strict_types=1);

namespace ChronicleKeeper\Library\Application\Query;

use ChronicleKeeper\Chat\Application\Query\FindConversationsByDirectoryParameters;
use ChronicleKeeper\Document\Application\Query\FindDocumentsByDirectory;
use ChronicleKeeper\Image\Application\Query\FindImagesByDirectory;
use ChronicleKeeper\Library\Domain\ValueObject\DirectoryContent\Directory;
use ChronicleKeeper\Library\Domain\ValueObject\DirectoryContent\Element;
use ChronicleKeeper\Shared\Application\Query\Query;
use ChronicleKeeper\Shared\Application\Query\QueryParameters;
use ChronicleKeeper\Shared\Application\Query\QueryService;

use function assert;
use function strcasecmp;
use function usort;

final class FindDirectoryContentQuery implements Query
{
    public function __construct(
        public readonly QueryService $queryService,
    ) {
    }

    public function query(QueryParameters $parameters): Directory
    {
        assert($parameters instanceof FindDirectoryContent);

        $searchDirectory = $this->queryService->query(new FindDirectoryById($parameters->id));
        $directory       = Directory::fromEntity($searchDirectory);

        $childDirectories = $this->queryService->query(new FindDirectoriesByParent($directory->id));
        foreach ($childDirectories as $childDirectory) {
            $directory->directories[] = Directory::fromEntity($childDirectory);
        }

        unset($childDirectories);

        // Sort directories by their title
        usort(
            $directory->directories,
            static fn (Directory $left, Directory $right) => strcasecmp($left->title, $right->title),
        );

        // Add documents to the cache
        $documents = $this->queryService->query(new FindDocumentsByDirectory($directory->id));
        foreach ($documents as $document) {
            $directory->elements[] = Element::fromDocumentEntity($document);
        }

        unset($documents);

        // Add images to the cache
        $images = $this->queryService->query(new FindImagesByDirectory($directory->id));
        foreach ($images as $image) {
            $directory->elements[] = Element::fromImageEntity($image);
        }

        unset($images);

        // Add conversations to the cache
        $conversations = $this->queryService->query(new FindConversationsByDirectoryParameters($searchDirectory));
        foreach ($conversations as $conversation) {
            $directory->elements[] = Element::fromConversationEntity($conversation);
        }

        unset($conversations);

        // Sort the elements that were added to the directory by slug
        usort(
            $directory->elements,
            static fn (Element $left, Element $right) => strcasecmp($left->slug, $right->slug),
        );

        return $directory;
    }
}
