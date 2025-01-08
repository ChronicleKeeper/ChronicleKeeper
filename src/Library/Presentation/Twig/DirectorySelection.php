<?php

declare(strict_types=1);

namespace ChronicleKeeper\Library\Presentation\Twig;

use ChronicleKeeper\Library\Application\Query\FindAllDirectories;
use ChronicleKeeper\Library\Domain\RootDirectory;
use ChronicleKeeper\Shared\Application\Query\QueryService;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

use function array_merge;
use function uasort;

#[AsTwigComponent('directorySelection')]
class DirectorySelection
{
    public string $name;
    public string $preSelected = RootDirectory::ID;

    public function __construct(
        private readonly QueryService $queryService,
    ) {
    }

    /** @return array<string, string> */
    public function getSortedList(): array
    {
        $rootDirectory = RootDirectory::get();
        $directories   = [$rootDirectory->getId() => $rootDirectory->getTitle()];

        $addedDirectories = [];
        foreach ($this->queryService->query(new FindAllDirectories()) as $foundDirectory) {
            $addedDirectories[$foundDirectory->getId()] = $foundDirectory->flattenHierarchyTitle();
        }

        $directories = array_merge($directories, $addedDirectories);

        uasort(
            $directories,
            static fn ($left, $right) => $left <=> $right,
        );

        return $directories;
    }
}
