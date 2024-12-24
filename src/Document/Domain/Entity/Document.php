<?php

declare(strict_types=1);

namespace ChronicleKeeper\Document\Domain\Entity;

use ChronicleKeeper\Document\Domain\Event\DocumentChangedContent;
use ChronicleKeeper\Document\Domain\Event\DocumentCreated;
use ChronicleKeeper\Document\Domain\Event\DocumentMovedToDirectory;
use ChronicleKeeper\Document\Domain\Event\DocumentRenamed;
use ChronicleKeeper\Library\Domain\Entity\Directory;
use ChronicleKeeper\Library\Domain\RootDirectory;
use ChronicleKeeper\Shared\Domain\Entity\AggregateRoot;
use ChronicleKeeper\Shared\Domain\Sluggable;
use DateTimeImmutable;
use DateTimeInterface;
use JsonSerializable;
use Symfony\Component\String\Slugger\AsciiSlugger;
use Symfony\Component\Uid\Uuid;

use function sha1;
use function strlen;

/**
 * @phpstan-type DocumentArray = array{
 *     id: string,
 *     title: string,
 *     content: string,
 *     directory?: string,
 *     last_updated?: string
 * }
 */
class Document extends AggregateRoot implements JsonSerializable, Sluggable
{
    public function __construct(
        private readonly string $id,
        private string $title,
        private string $content,
        private Directory $directory,
        private DateTimeImmutable $updatedAt,
    ) {
    }

    public static function create(string $title, string $content, Directory|null $directory = null): self
    {
        $document = new self(
            Uuid::v4()->toString(),
            $title,
            $content,
            $directory ?? RootDirectory::get(),
            new DateTimeImmutable(),
        );
        $document->record(new DocumentCreated($document));

        return $document;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function getDirectory(): Directory
    {
        return $this->directory;
    }

    public function getUpdatedAt(): DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function moveToDirectory(Directory $directory): void
    {
        if ($directory->id === $this->directory->id) {
            return;
        }

        $this->record(new DocumentMovedToDirectory($this, $this->directory));

        $this->directory = $directory;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function rename(string $title): void
    {
        if ($title === $this->title) {
            return;
        }

        $this->record(new DocumentRenamed($this, $this->title));

        $this->title     = $title;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function changeContent(string $content): void
    {
        if ($content === $this->content) {
            return;
        }

        $this->record(new DocumentChangedContent($this, $this->content));

        $this->content   = $content;
        $this->updatedAt = new DateTimeImmutable();
    }

    /** @return DocumentArray */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'content' => $this->content,
            'directory' => $this->directory->id,
            'last_updated' => $this->updatedAt->format(DateTimeInterface::ATOM),
        ];
    }

    /** @return DocumentArray */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    public function getSlug(): string
    {
        return (new AsciiSlugger('de'))->slug($this->title)->toString();
    }

    public function getSize(): int
    {
        return strlen($this->content);
    }

    public function getContentHash(): string
    {
        return sha1($this->content);
    }
}
