<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Image\Infrastructure\VectorStorage;

use ChronicleKeeper\Image\Infrastructure\VectorStorage\LibraryImageUpdater;
use ChronicleKeeper\Library\Infrastructure\Repository\FilesystemImageRepository;
use ChronicleKeeper\Library\Infrastructure\Repository\FilesystemVectorImageRepository;
use ChronicleKeeper\Shared\Infrastructure\LLMChain\EmbeddingCalculator;
use ChronicleKeeper\Test\Library\Domain\Entity\ImageBuilder;
use PhpLlm\LlmChain\Document\Vector;
use PhpLlm\LlmChain\Model\Response\VectorResponse;
use PhpLlm\LlmChain\PlatformInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use ReflectionClass;

#[CoversClass(LibraryImageUpdater::class)]
#[Small]
class LibraryImageUpdaterTest extends TestCase
{
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
