<?php

declare(strict_types=1);

namespace DZunke\NovDoc\Domain\Library\Image;

use DateTimeImmutable;
use DateTimeInterface;
use DZunke\NovDoc\Domain\Document\Directory;
use DZunke\NovDoc\Domain\Library\Directory\RootDirectory;
use Symfony\Component\Uid\Uuid;

use function sha1;

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
class Image
{
    public string $id;
    public Directory $directory;
    public DateTimeImmutable $updatedAt;

    public function __construct(
        public string $title,
        public string $mimeType,
        public string $encodedImage,
        public string $description,
    ) {
        $this->id        = Uuid::v4()->toString();
        $this->directory = RootDirectory::get();
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

    public function getImageUrl(): string
    {
        return 'data:' . $this->mimeType . ';base64,' . $this->encodedImage;
    }

    public function getDescriptionHash(): string
    {
        return sha1($this->description);
    }
}
