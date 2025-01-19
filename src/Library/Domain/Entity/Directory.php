<?php

declare(strict_types=1);

namespace ChronicleKeeper\Library\Domain\Entity;

use ChronicleKeeper\Library\Domain\Event\DirectoryCreated;
use ChronicleKeeper\Library\Domain\Event\DirectoryMovedToDirectory;
use ChronicleKeeper\Library\Domain\Event\DirectoryRenamed;
use ChronicleKeeper\Library\Domain\RootDirectory;
use ChronicleKeeper\Shared\Domain\Entity\AggregateRoot;
use JsonSerializable;
use Symfony\Component\Uid\Uuid;

use function array_pop;
use function array_reverse;
use function implode;

/**
 * @phpstan-type DirectoryArray = array{
 *     id: string,
 *     title: string,
 *     parent: string,
 * }
 */
class Directory extends AggregateRoot implements JsonSerializable
{
    public function __construct(
        private readonly string $id,
        private string $title,
        private Directory|null $parent = null,
    ) {
    }

    public static function create(string $title, Directory|null $parent = null): Directory
    {
        $directory = new self(
            Uuid::v4()->toString(),
            $title,
            $parent ?? RootDirectory::get(),
        );
        $directory->record(new DirectoryCreated($directory));

        return $directory;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getParent(): Directory|null
    {
        return $this->parent;
    }

    public function isRoot(): bool
    {
        return $this->id === RootDirectory::ID;
    }

    public function rename(string $title): void
    {
        if ($this->title === $title) {
            return;
        }

        $this->record(new DirectoryRenamed($this, $this->title));

        $this->title = $title;
    }

    public function moveToDirectory(Directory $parent): void
    {
        if (! $this->parent instanceof self) {
            // Moving is not possible, only the root directory should have this null and so it is fixed
            return;
        }

        if ($this->parent === $parent) {
            return;
        }

        $this->record(new DirectoryMovedToDirectory($this, $this->parent));

        $this->parent = $parent;
    }

    public function equals(Directory $directory): bool
    {
        return $this->id === $directory->id;
    }

    public function flattenHierarchyTitle(bool $withoutRoot = false): string
    {
        if ($this->id === RootDirectory::ID) {
            return $this->title;
        }

        $directory = $this;

        $components = [];
        do {
            $components[] = $directory->title;
            $directory    = $directory->parent;
        } while ($directory instanceof self);

        if ($withoutRoot === true) {
            array_pop($components);
        }

        return implode(' > ', array_reverse($components));
    }

    /** @return DirectoryArray */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'parent' => $this->parent->id ?? RootDirectory::ID,
        ];
    }

    /** @return DirectoryArray */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}
