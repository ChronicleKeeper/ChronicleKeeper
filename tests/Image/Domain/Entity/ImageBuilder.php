<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Image\Domain\Entity;

use ChronicleKeeper\Image\Domain\Entity\Image;
use ChronicleKeeper\Library\Domain\Entity\Directory;
use ChronicleKeeper\Library\Domain\RootDirectory;
use DateTimeImmutable;
use Symfony\Component\Uid\Uuid;

use function base64_encode;

class ImageBuilder
{
    private string $id;
    private string $title;
    private string $mimeType;
    private string $encodedImage;
    private string $description;
    private Directory $directory;
    private DateTimeImmutable $updatedAt;

    public function __construct()
    {
        $this->id           = Uuid::v4()->toString();
        $this->title        = 'Default Title';
        $this->mimeType     = 'image/png';
        $this->encodedImage = base64_encode('default image content');
        $this->description  = 'Default Description';
        $this->directory    = RootDirectory::get();
        $this->updatedAt    = new DateTimeImmutable();
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

    public function withMimeType(string $mimeType): self
    {
        $this->mimeType = $mimeType;

        return $this;
    }

    public function withEncodedImage(string $encodedImage): self
    {
        $this->encodedImage = $encodedImage;

        return $this;
    }

    public function withDescription(string $description): self
    {
        $this->description = $description;

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

    public function build(): Image
    {
        return new Image(
            $this->id,
            $this->title,
            $this->mimeType,
            $this->encodedImage,
            $this->description,
            $this->directory,
            $this->updatedAt,
        );
    }
}
