<?php

declare(strict_types=1);

namespace DZunke\NovDoc\Domain\Document;

use DZunke\NovDoc\Domain\Library\Directory\RootDirectory;
use Symfony\Component\Uid\Uuid;

use function array_key_exists;
use function array_reverse;
use function count;
use function implode;

/**
 * @phpstan-type DirectoryArray = array{
 *     id: string,
 *     title: string,
 *     parent: string,
 * }
 */
class Directory
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

    /**
     * @param mixed[] $array
     *
     * @phpstan-return ($array is DirectoryArray ? true : false)
     */
    public static function isDirectoryArray(array $array): bool
    {
        return count($array) === 3
            && array_key_exists('id', $array)
            && array_key_exists('title', $array)
            && array_key_exists('parent', $array);
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
}
