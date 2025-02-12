<?php

declare(strict_types=1);

namespace ChronicleKeeper\Library\Domain\ValueObject\DirectoryContent;

use ChronicleKeeper\Library\Domain\Entity\Directory as DirectoryEntity;

class Directory
{
    /**
     * @param list<Element>   $elements
     * @param list<Directory> $directories
     */
    public function __construct(
        public string $id,
        public string $title,
        public array $elements,
        public array $directories,
    ) {
    }

    public static function fromEntity(DirectoryEntity $directory): Directory
    {
        return new Directory($directory->getId(), $directory->getTitle(), [], []);
    }
}
