<?php

declare(strict_types=1);

namespace DZunke\NovDoc\Domain\Document;

use Symfony\Component\Uid\Uuid;

use function array_key_exists;
use function count;

/**
 * @phpstan-type DirectoryArray = array{
 *     id: string,
 *     title: string,
 *     parent: string|null,
 * }
 */
class Directory
{
    public string $id;
    public Directory|null $parent = null;

    public function __construct(public string $title)
    {
        $this->id = Uuid::v4()->toString();
    }

    public static function createForRoot(): Directory
    {
        return new Directory('Root');
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
            'parent' => $this->parent?->id,
        ];
    }
}
