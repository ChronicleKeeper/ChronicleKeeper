<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Document\Infrastructure\VectorStorage;

use ChronicleKeeper\Document\Application\Command\DeleteDocumentVectors;
use ChronicleKeeper\Document\Application\Command\StoreDocumentVectors;
use ChronicleKeeper\Document\Application\Query\FindAllDocuments;
use ChronicleKeeper\Document\Infrastructure\VectorStorage\LibraryDocumentUpdater;
use ChronicleKeeper\Shared\Application\Query\QueryParameters;
use ChronicleKeeper\Shared\Application\Query\QueryService;
use ChronicleKeeper\Shared\Infrastructure\LLMChain\EmbeddingCalculator;
use ChronicleKeeper\Test\Document\Domain\Entity\DocumentBuilder;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;

#[CoversClass(LibraryDocumentUpdater::class)]
#[Small]
class LibraryDocumentUpdaterTest extends TestCase
{
    #[Test]
    public function itDoesNothingWhenThereAreNoDocuments(): void
    {
        $queryService = $this->createMock(QueryService::class);
        $queryService->expects($this->once())
            ->method('query')
            ->with(self::isInstanceOf(FindAllDocuments::class))
            ->willReturn([]);

        $embeddingCalculator = $this->createMock(EmbeddingCalculator::class);
        $embeddingCalculator->expects($this->never())->method('createTextChunks');
        $embeddingCalculator->expects($this->never())->method('getMultipleEmbeddings');
        $embeddingCalculator->expects($this->never())->method('getMultipleEmbeddings');

        $bus = $this->createMock(MessageBusInterface::class);
        $bus->expects($this->never())->method('dispatch');

        $updater = new LibraryDocumentUpdater(new NullLogger(), $embeddingCalculator, $queryService, $bus);
        $updater->updateAll();
    }

    #[Test]
    public function itCreatesAVectorStorageIfThereWasNone(): void
    {
        $document = (new DocumentBuilder())->build();

        $queryService = $this->createMock(QueryService::class);
        $queryService->expects($this->once())
            ->method('query')
            ->willReturnCallback(
                static function (QueryParameters $query) use ($document) {
                    if ($query instanceof FindAllDocuments) {
                        return [$document];
                    }

                    throw new InvalidArgumentException('Unexpected query ' . $query::class);
                },
            );

        $embeddingCalculator = $this->createMock(EmbeddingCalculator::class);
        $embeddingCalculator->expects($this->never())->method('getSingleEmbedding');
        $embeddingCalculator->expects($this->once())
            ->method('getMultipleEmbeddings')
            ->willReturn([[10.12]]);
        $embeddingCalculator->expects($this->once())
            ->method('createTextChunks')
            ->willReturn(['foo']);

        $bus = $this->createMock(MessageBusInterface::class);
        $bus->expects($this->exactly(2))
            ->method('dispatch')
            ->willReturnCallback(
                static function (object $command) use ($document) {
                    if ($command instanceof DeleteDocumentVectors) {
                        self::assertSame($document->getId(), $command->documentId);
                    }

                    if ($command instanceof StoreDocumentVectors) {
                        self::assertSame([10.12], $command->vectorDocument->vector);
                    }

                    return new Envelope($command);
                },
            );

        $updater = new LibraryDocumentUpdater(new NullLogger(), $embeddingCalculator, $queryService, $bus);
        $updater->updateAll();
    }

    #[Test]
    public function itCleansTheVectorStorageAndRegeneratesIt(): void
    {
        $document = (new DocumentBuilder())->withId('8a998dc1-31bc-4903-8e60-6ad3232f819b')->build();

        $queryService = $this->createMock(QueryService::class);
        $queryService->expects($this->once())
            ->method('query')
            ->willReturnCallback(
                static function (QueryParameters $query) use ($document) {
                    if ($query instanceof FindAllDocuments) {
                        return [$document];
                    }

                    throw new InvalidArgumentException('Unexpected query');
                },
            );

        $embeddingCalculator = $this->createMock(EmbeddingCalculator::class);
        $embeddingCalculator->expects($this->never())->method('getSingleEmbedding');
        $embeddingCalculator->expects($this->once())
            ->method('getMultipleEmbeddings')
            ->willReturn([[10.12]]);
        $embeddingCalculator->expects($this->once())
            ->method('createTextChunks')
            ->willReturn([$document->getContent()]);

        $bus = $this->createMock(MessageBusInterface::class);
        $bus->expects($this->exactly(2))
            ->method('dispatch')
            ->willReturnCallback(
                static function (object $command) use ($document) {
                    if ($command instanceof DeleteDocumentVectors) {
                        self::assertSame($document->getId(), $command->documentId);
                    }

                    if ($command instanceof StoreDocumentVectors) {
                        self::assertSame([10.12], $command->vectorDocument->vector);
                    }

                    return new Envelope($command);
                },
            );

        $updater = new LibraryDocumentUpdater(new NullLogger(), $embeddingCalculator, $queryService, $bus);
        $updater->updateAll();
    }

    #[Test]
    public function itDoesSplitContentForMultipleVectorDocuments(): void
    {
        $queryService = $this->createMock(QueryService::class);
        $queryService->expects($this->once())
            ->method('query')
            ->willReturnCallback(
                static function (QueryParameters $query) {
                    if ($query instanceof FindAllDocuments) {
                        return [(new DocumentBuilder())->withContent('Foo Bar Baz')->build()];
                    }

                    throw new InvalidArgumentException('Unexpected query ' . $query::class);
                },
            );

        $embeddingCalculator = $this->createMock(EmbeddingCalculator::class);
        $embeddingCalculator->expects($this->once())
            ->method('createTextChunks')
            ->willReturn(['Foo', 'Bar', 'Baz']);
        $embeddingCalculator->expects($this->once())
            ->method('getMultipleEmbeddings')
            ->willReturn([[10.12], [11.13], [12.14]]);

        $busInvoker = $this->exactly(4);

        $bus = $this->createMock(MessageBusInterface::class);
        $bus->expects($busInvoker)
            ->method('dispatch')
            ->willReturnCallback(
                static function (StoreDocumentVectors|DeleteDocumentVectors $command) use ($busInvoker) {
                    if ($busInvoker->numberOfInvocations() === 1) {
                        self::assertInstanceOf(DeleteDocumentVectors::class, $command);
                    }

                    if ($busInvoker->numberOfInvocations() === 2) {
                        self::assertInstanceOf(StoreDocumentVectors::class, $command);
                        self::assertSame([10.12], $command->vectorDocument->vector);
                        self::assertSame('Foo', $command->vectorDocument->content);
                    }

                    if ($busInvoker->numberOfInvocations() === 3) {
                        self::assertInstanceOf(StoreDocumentVectors::class, $command);
                        self::assertSame([11.13], $command->vectorDocument->vector);
                        self::assertSame('Bar', $command->vectorDocument->content);
                    }

                    if ($busInvoker->numberOfInvocations() === 4) {
                        self::assertInstanceOf(StoreDocumentVectors::class, $command);
                        self::assertSame([12.14], $command->vectorDocument->vector);
                        self::assertSame('Baz', $command->vectorDocument->content);
                    }

                    return new Envelope($command);
                },
            );

        $updater = new LibraryDocumentUpdater(new NullLogger(), $embeddingCalculator, $queryService, $bus);
        $updater->updateAll(1);
    }
}
