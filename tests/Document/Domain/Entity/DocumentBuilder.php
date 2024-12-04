<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Document\Domain\Entity;

use ChronicleKeeper\Document\Domain\Entity\Document;
use ChronicleKeeper\Library\Domain\Entity\Directory;
use ChronicleKeeper\Library\Domain\RootDirectory;
use DateTimeImmutable;
use Symfony\Component\Uid\Uuid;

class DocumentBuilder
{
    private string $id;
    private string $title;
    private string $content;
    private Directory $directory;
    private DateTimeImmutable $updatedAt;

    public function __construct()
    {
        $this->id        = Uuid::v4()->toString();
        $this->title     = 'Default Title';
        $this->content   = 'Default Content';
        $this->directory = RootDirectory::get(); // Assuming a method to create a root directory
        $this->updatedAt = new DateTimeImmutable();
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
        $document->id        = $this->id;
        $document->directory = $this->directory;
        $document->updatedAt = $this->updatedAt;

        return $document;
    }
}
