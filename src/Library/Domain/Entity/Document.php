<?php

declare(strict_types=1);

namespace ChronicleKeeper\Library\Domain\Entity;

use ChronicleKeeper\Library\Domain\RootDirectory;
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
class Document implements JsonSerializable, Sluggable
{
    public string $id;
    public DateTimeImmutable $updatedAt;
    public Directory $directory;

    public function __construct(public string $title, public string $content)
    {
        $this->id        = Uuid::v4()->toString();
        $this->directory = RootDirectory::get();
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
