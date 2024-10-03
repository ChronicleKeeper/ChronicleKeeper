<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Library\Domain\Entity;

use ChronicleKeeper\Library\Domain\Entity\Directory;
use Symfony\Component\Uid\Uuid;

class DirectoryBuilder
{
    private string $id;
    private string $title;
    private Directory|null $parent = null;

    public function __construct()
    {
        $this->id    = Uuid::v4()->toString();
        $this->title = 'Default Title';
    }

    public function withId(string $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function withTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function withParent(Directory $parent): self
    {
        $this->parent = $parent;

        return $this;
    }

    public function build(): Directory
    {
        $directory         = new Directory($this->title);
        $directory->id     = $this->id;
        $directory->parent = $this->parent;

        return $directory;
    }
}
