<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Library\Infrastructure\VectorStorage\Updater;

use ChronicleKeeper\Library\Infrastructure\Repository\FilesystemDocumentRepository;
use ChronicleKeeper\Library\Infrastructure\Repository\FilesystemVectorDocumentRepository;
use ChronicleKeeper\Library\Infrastructure\VectorStorage\Updater\LibraryDocumentUpdater;
use ChronicleKeeper\Test\Library\Domain\Entity\DocumentBuilder;
use PhpLlm\LlmChain\Document\Vector;
use PhpLlm\LlmChain\EmbeddingsModel;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use ReflectionClass;

#[CoversClass(LibraryDocumentUpdater::class)]
#[Small]
class LibraryDocumentUpdaterTest extends TestCase
{
    #[Test]
    public function splitDocumentIntoVectorChunks(): void
    {
        $document = (new DocumentBuilder())->withContent('This is a test document.')->build();

        $embeddingModel = self::createStub(EmbeddingsModel::class);
        $embeddingModel->method('create')->willReturn(new Vector([0.1, 0.2, 0.3]));

        $updater = new LibraryDocumentUpdater(
            self::createStub(LoggerInterface::class),
            $embeddingModel,
            self::createStub(FilesystemDocumentRepository::class),
            self::createStub(FilesystemVectorDocumentRepository::class),
        );

        $reflection = new ReflectionClass(LibraryDocumentUpdater::class);
        $method     = $reflection->getMethod('splitDocumentInVectorDocuments');

        $chunks = $method->invoke($updater, $document, 2);

        self::assertCount(5, $chunks); // 5 Parts because the content should be split with full words

        self::assertSame('This', $chunks[0]->content);
        self::assertSame('is', $chunks[1]->content);
        self::assertSame('a', $chunks[2]->content);
        self::assertSame('test', $chunks[3]->content);
        self::assertSame('document.', $chunks[4]->content);
    }
}
