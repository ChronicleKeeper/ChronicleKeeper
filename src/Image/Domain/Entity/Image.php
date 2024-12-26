<?php

declare(strict_types=1);

namespace ChronicleKeeper\Image\Domain\Entity;

use ChronicleKeeper\Image\Domain\Event\ImageCreated;
use ChronicleKeeper\Image\Domain\Event\ImageDescriptionUpdated;
use ChronicleKeeper\Image\Domain\Event\ImageMovedToDirectory;
use ChronicleKeeper\Image\Domain\Event\ImageRenamed;
use ChronicleKeeper\Library\Domain\Entity\Directory;
use ChronicleKeeper\Library\Domain\RootDirectory;
use ChronicleKeeper\Shared\Domain\Entity\AggregateRoot;
use ChronicleKeeper\Shared\Domain\Sluggable;
use DateTimeImmutable;
use DateTimeInterface;
use JsonSerializable;
use Symfony\Component\String\Slugger\AsciiSlugger;
use Symfony\Component\Uid\Uuid;

use function base64_decode;
use function sha1;
use function strlen;

/**
 * @phpstan-type ImageArray = array{
 *     id: string,
 *     title: string,
 *     mime_type: string,
 *     encoded_image: string,
 *     directory: string,
 *     description: string,
 *     last_updated: string
 * }
 */
class Image extends AggregateRoot implements JsonSerializable, Sluggable
{
    public function __construct(
        private readonly string $id,
        private string $title,
        private readonly string $mimeType,
        private readonly string $encodedImage,
        private string $description,
        private Directory $directory,
        private DateTimeImmutable $updatedAt,
    ) {
    }

    public static function create(
        string $title,
        string $mimeType,
        string $encodedImage,
        string $description,
        Directory|null $directory = null,
    ): Image {
        $image = new self(
            Uuid::v4()->toString(),
            $title,
            $mimeType,
            $encodedImage,
            $description,
            $directory ?? RootDirectory::get(),
            new DateTimeImmutable(),
        );
        $image->record(new ImageCreated($image));

        return $image;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function getDirectory(): Directory
    {
        return $this->directory;
    }

    public function getMimeType(): string
    {
        return $this->mimeType;
    }

    public function getEncodedImage(): string
    {
        return $this->encodedImage;
    }

    public function getUpdatedAt(): DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function rename(string $title): void
    {
        if ($title === $this->title) {
            return;
        }

        $this->record(new ImageRenamed($this, $this->title));
        $this->title     = $title;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function updateDescription(string $description): void
    {
        if ($description === $this->description) {
            return;
        }

        $this->record(new ImageDescriptionUpdated($this, $this->description));
        $this->description = $description;
        $this->updatedAt   = new DateTimeImmutable();
    }

    public function moveToDirectory(Directory $directory): void
    {
        if ($directory->id === $this->directory->id) {
            return;
        }

        $this->record(new ImageMovedToDirectory($this, $this->directory));
        $this->directory = $directory;
        $this->updatedAt = new DateTimeImmutable();
    }

    /** @return ImageArray */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'mime_type' => $this->mimeType,
            'encoded_image' => $this->encodedImage,
            'description' => $this->description,
            'directory' => $this->directory->id,
            'last_updated' => $this->updatedAt->format(DateTimeInterface::ATOM),
        ];
    }

    /** @return ImageArray */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    public function getSlug(): string
    {
        return (new AsciiSlugger('de'))->slug($this->title)->toString();
    }

    public function getImageUrl(): string
    {
        return 'data:' . $this->mimeType . ';base64,' . $this->encodedImage;
    }

    public function getDescriptionHash(): string
    {
        return sha1($this->description);
    }

    public function getSize(): int
    {
        $decodedImage = base64_decode($this->encodedImage, true);
        if ($decodedImage === false) {
            return 0;
        }

        return strlen($decodedImage);
    }
}
