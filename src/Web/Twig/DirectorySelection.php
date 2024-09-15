<?php

declare(strict_types=1);

namespace DZunke\NovDoc\Web\Twig;

use DZunke\NovDoc\Domain\Document\Directory;
use DZunke\NovDoc\Domain\Library\Directory\RootDirectory;
use DZunke\NovDoc\Infrastructure\Repository\FilesystemDirectoryRepository;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

use function array_merge;
use function array_reverse;
use function implode;
use function uasort;

#[AsTwigComponent('directorySelection')]
class DirectorySelection
{
    public string $name;
    public string $preSelected = RootDirectory::ID;

    public function __construct(
        private readonly FilesystemDirectoryRepository $directoryRepository,
    ) {
    }

    /** @return array<string, string> */
    public function getSortedList(): array
    {
        $rootDirectory = RootDirectory::get();
        $directories   = [$rootDirectory->id => $rootDirectory->title];

        $addedDirectories = [];
        foreach ($this->directoryRepository->findAll() as $foundDirectory) {
            $addedDirectories[$foundDirectory->id] = $this->flattenHierarchyTitle($foundDirectory);
        }

        $directories = array_merge($directories, $addedDirectories);

        uasort(
            $directories,
            static fn ($left, $right) => $left <=> $right,
        );

        return $directories;
    }

    private function flattenHierarchyTitle(Directory $directory): string
    {
        $components = [];
        do {
            $components[] = $directory->title;
            $directory    = $directory->parent;
        } while ($directory !== null);

        return implode(' > ', array_reverse($components));
    }
}
