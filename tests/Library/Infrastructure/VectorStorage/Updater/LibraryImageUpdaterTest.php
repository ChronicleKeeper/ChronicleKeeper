<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Library\Infrastructure\VectorStorage\Updater;

use ChronicleKeeper\Library\Infrastructure\Repository\FilesystemImageRepository;
use ChronicleKeeper\Library\Infrastructure\Repository\FilesystemVectorImageRepository;
use ChronicleKeeper\Library\Infrastructure\VectorStorage\Updater\LibraryImageUpdater;
use ChronicleKeeper\Test\Library\Domain\Entity\ImageBuilder;
use PhpLlm\LlmChain\Document\Vector;
use PhpLlm\LlmChain\EmbeddingsModel;
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
    public function splitDocumentIntoVectorChunks(): void
    {
        $image = (new ImageBuilder())->withDescription('This is a test image.')->build();

        $embeddingModel = self::createStub(EmbeddingsModel::class);
        $embeddingModel->method('create')->willReturn(new Vector([0.1, 0.2, 0.3]));

        $updater = new LibraryImageUpdater(
            self::createStub(LoggerInterface::class),
            $embeddingModel,
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
        self::assertSame('image.', $chunks[4]->content);
    }
}
