<?php

declare(strict_types=1);

namespace ChronicleKeeper\Library\Domain\Entity;

use ChronicleKeeper\Library\Domain\RootDirectory;
use DateTimeImmutable;
use DateTimeInterface;
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
