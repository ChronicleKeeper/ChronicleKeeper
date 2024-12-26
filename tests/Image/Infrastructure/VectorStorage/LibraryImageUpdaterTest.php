<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Image\Infrastructure\VectorStorage;

use ChronicleKeeper\Image\Infrastructure\VectorStorage\LibraryImageUpdater;
use ChronicleKeeper\Library\Infrastructure\Repository\FilesystemImageRepository;
use ChronicleKeeper\Library\Infrastructure\Repository\FilesystemVectorImageRepository;
use ChronicleKeeper\Library\Infrastructure\VectorStorage\VectorImage;
use ChronicleKeeper\Shared\Infrastructure\LLMChain\EmbeddingCalculator;
use ChronicleKeeper\Test\Image\Domain\Entity\ImageBuilder;
use ChronicleKeeper\Test\Image\Domain\Entity\VectorImageBuilder;
use PhpLlm\LlmChain\Document\Vector;
use PhpLlm\LlmChain\Model\Response\VectorResponse;
use PhpLlm\LlmChain\PlatformInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use ReflectionClass;

#[CoversClass(LibraryImageUpdater::class)]
#[Small]
class LibraryImageUpdaterTest extends TestCase
{
    #[Test]
    public function itDoesNothingWhenThereAreNoImages(): void
    {
        $imageRepository = $this->createMock(FilesystemImageRepository::class);
        $imageRepository->expects($this->once())
            ->method('findAll')
            ->willReturn([]);

        $vectorImageRepository = $this->createMock(FilesystemVectorImageRepository::class);
        $vectorImageRepository->expects($this->never())->method('findAllByImageId');
        $vectorImageRepository->expects($this->never())->method('store');

        $embeddingCalculator = $this->createMock(EmbeddingCalculator::class);
        $embeddingCalculator->expects($this->never())->method('createTextChunks');
        $embeddingCalculator->expects($this->never())->method('getMultipleEmbeddings');

        $updater = new LibraryImageUpdater(
            new NullLogger(),
            $embeddingCalculator,
            $imageRepository,
            $vectorImageRepository,
        );
        $updater->updateAll();
    }

    #[Test]
    public function itCreatesAVectorStorageIfThereWasNone(): void
    {
        $image = (new ImageBuilder())
            ->withId('4aa2c8d3-3b74-451c-b19e-33d1f80227d5')
            ->withDescription('This is a test image.')
            ->build();

        $imageRepository = $this->createMock(FilesystemImageRepository::class);
        $imageRepository->expects($this->once())
            ->method('findAll')
            ->willReturn([$image]);

        $vectorImageRepository = $this->createMock(FilesystemVectorImageRepository::class);
        $vectorImageRepository->expects($this->once())
            ->method('findAllByImageId')
            ->with('4aa2c8d3-3b74-451c-b19e-33d1f80227d5')
            ->willReturn([]);

        $invoker = $this->exactly(2);
        $vectorImageRepository->expects($invoker)
            ->method('store')
            ->willReturnCallback(static function (VectorImage $vectorImage) use ($invoker): void {
                if ($invoker->numberOfInvocations() === 1) {
                    self::assertSame('This is a', $vectorImage->content);
                    self::assertSame([10.1], $vectorImage->vector);
                } else {
                    self::assertSame('test image.', $vectorImage->content);
                    self::assertSame([10.2], $vectorImage->vector);
                }
            });

        $embeddingCalculator = $this->createMock(EmbeddingCalculator::class);
        $embeddingCalculator->expects($this->once())
            ->method('createTextChunks')
            ->with('This is a test image.')
            ->willReturn(['This is a', 'test image.']);

        $embeddingCalculator->expects($this->once())
            ->method('getMultipleEmbeddings')
            ->with(['This is a', 'test image.'])
            ->willReturn([[10.1], [10.2]]);

        $updater = new LibraryImageUpdater(
            new NullLogger(),
            $embeddingCalculator,
            $imageRepository,
            $vectorImageRepository,
        );
        $updater->updateAll();
    }

    #[Test]
    public function itDoesNothingForExistingStorageOnUnchangedContentHash(): void
    {
        $image = (new ImageBuilder())
            ->withId('4aa2c8d3-3b74-451c-b19e-33d1f80227d5')
            ->withDescription('This is a test image.')
            ->build();

        $vectorImage = (new VectorImageBuilder())
            ->withImage($image)
            ->withVectorContentHash($image->getDescriptionHash())
            ->withVector([10.1])
            ->build();

        $imageRepository = $this->createMock(FilesystemImageRepository::class);
        $imageRepository->expects($this->once())
            ->method('findAll')
            ->willReturn([$image]);

        $vectorImageRepository = $this->createMock(FilesystemVectorImageRepository::class);
        $vectorImageRepository->expects($this->once())
            ->method('findAllByImageId')
            ->with('4aa2c8d3-3b74-451c-b19e-33d1f80227d5')
            ->willReturn([$vectorImage]);
        $vectorImageRepository->expects($this->never())->method('store');

        $embeddingCalculator = $this->createMock(EmbeddingCalculator::class);
        $embeddingCalculator->expects($this->never())->method('createTextChunks');
        $embeddingCalculator->expects($this->never())->method('getMultipleEmbeddings');

        $updater = new LibraryImageUpdater(
            new NullLogger(),
            $embeddingCalculator,
            $imageRepository,
            $vectorImageRepository,
        );
        $updater->updateAll();
    }

    #[Test]
    public function itCleansTheVectorStorageAndRegeneratesIt(): void
    {
        $image = (new ImageBuilder())
            ->withId('4aa2c8d3-3b74-451c-b19e-33d1f80227d5')
            ->withDescription('This is a test image.')
            ->build();

        $vectorImage = (new VectorImageBuilder())
            ->withImage($image)
            ->withVectorContentHash('12345')
            ->withVector([10.1])
            ->build();

        $imageRepository = $this->createMock(FilesystemImageRepository::class);
        $imageRepository->expects($this->once())
            ->method('findAll')
            ->willReturn([$image]);

        $vectorImageRepository = $this->createMock(FilesystemVectorImageRepository::class);
        $vectorImageRepository->expects($this->once())
            ->method('findAllByImageId')
            ->with('4aa2c8d3-3b74-451c-b19e-33d1f80227d5')
            ->willReturn([$vectorImage]);

        $invoker = $this->exactly(2);
        $vectorImageRepository->expects($invoker)
            ->method('store')
            ->willReturnCallback(static function (VectorImage $vectorImage) use ($invoker): void {
                if ($invoker->numberOfInvocations() === 1) {
                    self::assertSame('This is a', $vectorImage->content);
                    self::assertSame([10.1], $vectorImage->vector);
                } else {
                    self::assertSame('test image.', $vectorImage->content);
                    self::assertSame([10.2], $vectorImage->vector);
                }
            });

        $embeddingCalculator = $this->createMock(EmbeddingCalculator::class);
        $embeddingCalculator->expects($this->once())
            ->method('createTextChunks')
            ->with('This is a test image.')
            ->willReturn(['This is a', 'test image.']);

        $embeddingCalculator->expects($this->once())
            ->method('getMultipleEmbeddings')
            ->with(['This is a', 'test image.'])
            ->willReturn([[10.1], [10.2]]);

        $updater = new LibraryImageUpdater(
            new NullLogger(),
            $embeddingCalculator,
            $imageRepository,
            $vectorImageRepository,
        );
        $updater->updateAll();
    }

    #[Test]
    public function splitImagesIntoVectorChunks(): void
    {
        $image = (new ImageBuilder())->withDescription('This is a test document.')->build();

        $platform = self::createStub(PlatformInterface::class);
        $platform->method('request')->willReturn(new VectorResponse(new Vector([0.1, 0.2, 0.3])));

        $embeddingCalculator = $this->createMock(EmbeddingCalculator::class);
        $embeddingCalculator->expects($this->once())
            ->method('createTextChunks')
            ->with('This is a test document.')
            ->willReturn(['This', 'is', 'a', 'test', 'document.']);
        $embeddingCalculator->expects($this->once())
            ->method('getMultipleEmbeddings')
            ->with(['This', 'is', 'a', 'test', 'document.'])
            ->willReturn([[10.1], [10.2], [10.3], [10.4], [10.5]]);

        $updater = new LibraryImageUpdater(
            self::createStub(LoggerInterface::class),
            $embeddingCalculator,
            self::createStub(FilesystemImageRepository::class),
            self::createStub(FilesystemVectorImageRepository::class),
        );

        $reflection = new ReflectionClass(LibraryImageUpdater::class);
        $method     = $reflection->getMethod('splitImageDescriptionInVectorDocuments');

        $chunks = $method->invoke($updater, $image, 2);

        self::assertCount(5, $chunks); // 5 Parts because the content should be split with full words

        self::assertSame('This', $chunks[0]->content);
        self::assertSame('is', $chunks[1]->content);
        self::assertSame('a', $chunks[2]->content);
        self::assertSame('test', $chunks[3]->content);
        self::assertSame('document.', $chunks[4]->content);
    }
}
