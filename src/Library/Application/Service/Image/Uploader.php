<?php

declare(strict_types=1);

namespace DZunke\NovDoc\Library\Application\Service\Image;

use DZunke\NovDoc\Library\Domain\Entity\Directory;
use DZunke\NovDoc\Library\Domain\Entity\Image;
use DZunke\NovDoc\Library\Domain\RootDirectory;
use DZunke\NovDoc\Library\Infrastructure\Repository\FilesystemImageRepository;
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
        if ($targetDirectory === null) {
            $targetDirectory = RootDirectory::get();
        }

        $base64Image = base64_encode($file->getContent());
        $mimeType    = $file->getMimeType();

        if (! is_string($mimeType)) {
            throw new RuntimeException('Image seems to be defect, no mime type detected.');
        }

        $image = new Image(
            $file->getClientOriginalName(),
            $mimeType,
            $base64Image,
            '',
        );

        $image->description = $this->LLMDescriber->getDescription($image);
        $image->directory   = $targetDirectory;

        $this->imageRepository->store($image);

        return $image;
    }
}
