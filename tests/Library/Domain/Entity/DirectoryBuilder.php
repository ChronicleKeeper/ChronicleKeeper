<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Library\Domain\Entity;

use ChronicleKeeper\Library\Domain\Entity\Directory;
use ChronicleKeeper\Library\Domain\RootDirectory;
use Symfony\Component\Uid\Uuid;

class DirectoryBuilder
{
    private string $id;
    private string $title;
    private Directory|null $parent = null;

    public function __construct()
    {
        $this->id     = Uuid::v4()->toString();
        $this->title  = 'Default Title';
        $this->parent = RootDirectory::get();
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
        return new Directory($this->id, $this->title, $this->parent);
    }
}
