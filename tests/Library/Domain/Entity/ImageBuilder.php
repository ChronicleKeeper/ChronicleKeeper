<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Library\Domain\Entity;

use ChronicleKeeper\Library\Domain\Entity\Directory;
use ChronicleKeeper\Library\Domain\Entity\Image;
use ChronicleKeeper\Library\Domain\RootDirectory;
use DateTimeImmutable;

use function base64_encode;

class ImageBuilder
{
    private string $title;
    private string $mimeType;
    private string $encodedImage;
    private string $description;
    private Directory $directory;
    private DateTimeImmutable $updatedAt;

    public function __construct()
    {
        $this->title        = 'Default Title';
        $this->mimeType     = 'image/png';
        $this->encodedImage = base64_encode('default image content');
        $this->description  = 'Default Description';
        $this->directory    = RootDirectory::get();
        $this->updatedAt    = new DateTimeImmutable();
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
        $image            = new Image(
            $this->title,
            $this->mimeType,
            $this->encodedImage,
            $this->description,
        );
        $image->directory = $this->directory;
        $image->updatedAt = $this->updatedAt;

        return $image;
    }
}
