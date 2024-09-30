<?php

declare(strict_types=1);

namespace ChronicleKeeper\Library\Domain\Entity;

use ChronicleKeeper\Library\Domain\RootDirectory;
use JsonSerializable;
use Symfony\Component\Uid\Uuid;

use function array_reverse;
use function implode;

/**
 * @phpstan-type DirectoryArray = array{
 *     id: string,
 *     title: string,
 *     parent: string,
 * }
 */
class Directory implements JsonSerializable
{
    public string $id;
    public Directory|null $parent;

    public function __construct(public string $title)
    {
        $this->id     = Uuid::v4()->toString();
        $this->parent = RootDirectory::get(); // Initially root directory
    }

    public function flattenHierarchyTitle(): string
    {
        $directory = $this;

        $components = [];
        do {
            $components[] = $directory->title;
            $directory    = $directory->parent;
        } while ($directory !== null);

        return implode(' > ', array_reverse($components));
    }

    /** @return DirectoryArray */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'parent' => $this->parent?->id ?? RootDirectory::ID,
        ];
    }

    /** @return DirectoryArray */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}
