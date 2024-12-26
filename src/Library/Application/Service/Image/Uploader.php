<?php

declare(strict_types=1);

namespace ChronicleKeeper\Library\Application\Service\Image;

use ChronicleKeeper\Image\Domain\Entity\Image;
use ChronicleKeeper\Library\Domain\Entity\Directory;
use ChronicleKeeper\Library\Domain\RootDirectory;
use ChronicleKeeper\Library\Infrastructure\Repository\FilesystemImageRepository;
use RuntimeException;
use Symfony\Component\HttpFoundation\File\UploadedFile;

use function base64_encode;
use function is_string;

class Uploader
{
    public function __construct(
        private readonly LLMDescriber $LLMDescriber,
        private readonly FilesystemImageRepository $imageRepository,
    ) {
    }

    public function upload(UploadedFile $file, Directory|null $targetDirectory = null): Image
    {
        if (! $targetDirectory instanceof Directory) {
            $targetDirectory = RootDirectory::get();
        }

        $base64Image = base64_encode($file->getContent());
        $mimeType    = $file->getMimeType();

        if (! is_string($mimeType)) {
            throw new RuntimeException('Image seems to be defect, no mime type detected.');
        }

        $image = Image::create(
            $file->getClientOriginalName(),
            $mimeType,
            $base64Image,
            '',
            $targetDirectory,
        );
        $image->updateDescription($this->LLMDescriber->getDescription($image));

        $this->imageRepository->store($image);

        return $image;
    }
}
