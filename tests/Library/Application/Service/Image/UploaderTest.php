<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Library\Application\Service\Image;

use ChronicleKeeper\Image\Domain\Entity\Image;
use ChronicleKeeper\Library\Application\Service\Image\LLMDescriber;
use ChronicleKeeper\Library\Application\Service\Image\Uploader;
use ChronicleKeeper\Library\Domain\RootDirectory;
use ChronicleKeeper\Library\Infrastructure\Repository\FilesystemImageRepository;
use ChronicleKeeper\Settings\Domain\Entity\SystemPrompt;
use ChronicleKeeper\Test\Image\Domain\Entity\ImageBuilder;
use ChronicleKeeper\Test\Library\Domain\Entity\DirectoryBuilder;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Symfony\Component\HttpFoundation\File\UploadedFile;

#[CoversClass(Uploader::class)]
#[Small]
final class UploaderTest extends TestCase
{
    #[Test]
    public function itUploadsAnImageToTheRootWhenNoDirectoryGiven(): void
    {
        $llmDescriber = $this->createMock(LLMDescriber::class);
        $llmDescriber
            ->expects($this->once())
            ->method('copyImageWithGeneratedDescription')
            ->willReturn((new ImageBuilder())->build());

        $imageRepository = $this->createMock(FilesystemImageRepository::class);
        $imageRepository
            ->expects($this->once())
            ->method('store');

        $uploadedFile = self::createStub(UploadedFile::class);
        $uploadedFile->method('getContent')->willReturn('content');
        $uploadedFile->method('getMimeType')->willReturn('image/png');
        $uploadedFile->method('getClientOriginalName')->willReturn('image.png');

        $uploader       = new Uploader($llmDescriber, $imageRepository);
        $resultingImage = $uploader->upload($uploadedFile, $this->createMock(SystemPrompt::class));

        self::assertSame(RootDirectory::ID, $resultingImage->getDirectory()->getId());
    }

    #[Test]
    public function itUploadsAnImageToTheGivenDirectory(): void
    {
        $directory = (new DirectoryBuilder())->build();

        $llmDescriber = $this->createMock(LLMDescriber::class);
        $llmDescriber
            ->expects($this->once())
            ->method('copyImageWithGeneratedDescription')
            ->willReturnCallback(
                static function (Image $image) use ($directory): Image {
                    self::assertSame('image.png', $image->getTitle());
                    self::assertSame('image/png', $image->getMimeType());
                    self::assertSame('Y29udGVudA==', $image->getEncodedImage());
                    self::assertSame($directory, $image->getDirectory());

                    return (new ImageBuilder())->build();
                },
            );

        $imageRepository = $this->createMock(FilesystemImageRepository::class);
        $imageRepository
            ->expects($this->once())
            ->method('store');

        $uploadedFile = self::createStub(UploadedFile::class);
        $uploadedFile->method('getContent')->willReturn('content');
        $uploadedFile->method('getMimeType')->willReturn('image/png');
        $uploadedFile->method('getClientOriginalName')->willReturn('image.png');

        $uploader       = new Uploader($llmDescriber, $imageRepository);
        $resultingImage = $uploader->upload($uploadedFile, $this->createMock(SystemPrompt::class), $directory);

        self::assertSame(RootDirectory::ID, $resultingImage->getDirectory()->getId());
    }

    #[Test]
    public function itThrowsAnExceptionWhenImageIsDefect(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Image seems to be defect, no mime type detected.');

        $llmDescriber = $this->createMock(LLMDescriber::class);
        $llmDescriber->expects($this->never())->method('copyImageWithGeneratedDescription');

        $imageRepository = $this->createMock(FilesystemImageRepository::class);
        $imageRepository->expects($this->never())->method('store');

        $uploadedFile = self::createStub(UploadedFile::class);
        $uploadedFile->method('getContent')->willReturn('content');
        $uploadedFile->method('getMimeType')->willReturn(null);
        $uploadedFile->method('getClientOriginalName')->willReturn('image.png');

        $uploader = new Uploader($llmDescriber, $imageRepository);
        $uploader->upload($uploadedFile, $this->createMock(SystemPrompt::class));
    }
}
