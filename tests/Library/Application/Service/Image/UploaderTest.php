<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Library\Application\Service\Image;

use ChronicleKeeper\Image\Application\Command\StoreImage;
use ChronicleKeeper\Image\Domain\Entity\Image;
use ChronicleKeeper\Library\Application\Service\Image\LLMDescriber;
use ChronicleKeeper\Library\Application\Service\Image\Uploader;
use ChronicleKeeper\Library\Domain\RootDirectory;
use ChronicleKeeper\Settings\Domain\Entity\SystemPrompt;
use ChronicleKeeper\Test\Image\Domain\Entity\ImageBuilder;
use ChronicleKeeper\Test\Library\Domain\Entity\DirectoryBuilder;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;

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

        $bus = $this->createMock(MessageBusInterface::class);
        $bus->expects($this->once())
            ->method('dispatch')
            ->with(self::callback(static function (StoreImage $command): bool {
                $image = $command->image;

                self::assertSame('Default Title', $image->getTitle());
                self::assertSame('image/png', $image->getMimeType());
                self::assertSame('ZGVmYXVsdCBpbWFnZSBjb250ZW50', $image->getEncodedImage());
                self::assertSame(RootDirectory::ID, $image->getDirectory()->getId());

                return true;
            }))
            ->willReturn(new Envelope(new StoreImage(self::createStub(Image::class))));

        $uploadedFile = self::createStub(UploadedFile::class);
        $uploadedFile->method('getContent')->willReturn('content');
        $uploadedFile->method('getMimeType')->willReturn('image/png');
        $uploadedFile->method('getClientOriginalName')->willReturn('image.png');

        $uploader       = new Uploader($llmDescriber, $bus);
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

        $bus = $this->createMock(MessageBusInterface::class);
        $bus->expects($this->once())
            ->method('dispatch')
            ->with(self::callback(static function (StoreImage $command): bool {
                $image = $command->image;

                self::assertSame('Default Title', $image->getTitle());
                self::assertSame('image/png', $image->getMimeType());
                self::assertSame('ZGVmYXVsdCBpbWFnZSBjb250ZW50', $image->getEncodedImage());
                self::assertSame('caf93493-9072-44e2-a6db-4476985a849d', $image->getDirectory()->getId());

                return true;
            }))
            ->willReturn(new Envelope(new StoreImage(self::createStub(Image::class))));

        $uploadedFile = self::createStub(UploadedFile::class);
        $uploadedFile->method('getContent')->willReturn('content');
        $uploadedFile->method('getMimeType')->willReturn('image/png');
        $uploadedFile->method('getClientOriginalName')->willReturn('image.png');

        $uploader       = new Uploader($llmDescriber, $bus);
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

        $bus = $this->createMock(MessageBusInterface::class);
        $bus->expects($this->never())->method('dispatch');

        $uploadedFile = self::createStub(UploadedFile::class);
        $uploadedFile->method('getContent')->willReturn('content');
        $uploadedFile->method('getMimeType')->willReturn(null);
        $uploadedFile->method('getClientOriginalName')->willReturn('image.png');

        $uploader = new Uploader($llmDescriber, $bus);
        $uploader->upload($uploadedFile, $this->createMock(SystemPrompt::class));
    }
}
