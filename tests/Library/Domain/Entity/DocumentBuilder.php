<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Library\Domain\Entity;

use ChronicleKeeper\Library\Domain\Entity\Directory;
use ChronicleKeeper\Library\Domain\Entity\Document;
use ChronicleKeeper\Library\Domain\RootDirectory;
use DateTimeImmutable;

class DocumentBuilder
{
    private string $title;
    private string $content;
    private Directory $directory;
    private DateTimeImmutable $updatedAt;

    public function __construct()
    {
        $this->title     = 'Default Title';
        $this->content   = 'Default Content';
        $this->directory = RootDirectory::get(); // Assuming a method to create a root directory
        $this->updatedAt = new DateTimeImmutable();
    }

    public static function create(): self
    {
        return new self();
    }

    public function withTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function withContent(string $content): self
    {
        $this->content = $content;

        return $this;
    }

    public function withDirectory(Directory $directory): self
    {
        $this->directory = $directory;

        return $this;
    }

    public function withUpdatedAt(DateTimeImmutable $updatedAt): self
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    public function build(): Document
    {
        $document            = new Document($this->title, $this->content);
        $document->directory = $this->directory;
        $document->updatedAt = $this->updatedAt;

        return $document;
    }
}
