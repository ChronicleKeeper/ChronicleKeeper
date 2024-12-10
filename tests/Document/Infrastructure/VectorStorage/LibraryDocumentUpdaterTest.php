<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Document\Infrastructure\VectorStorage;

use ChronicleKeeper\Document\Application\Command\DeleteDocumentVectors;
use ChronicleKeeper\Document\Application\Command\StoreDocumentVectors;
use ChronicleKeeper\Document\Application\Query\FindAllDocuments;
use ChronicleKeeper\Document\Application\Query\FindVectorsOfDocument;
use ChronicleKeeper\Document\Infrastructure\VectorStorage\LibraryDocumentUpdater;
use ChronicleKeeper\Shared\Application\Query\QueryParameters;
use ChronicleKeeper\Shared\Application\Query\QueryService;
use ChronicleKeeper\Shared\Infrastructure\LLMChain\EmbeddingCalculator;
use ChronicleKeeper\Test\Document\Domain\Entity\DocumentBuilder;
use ChronicleKeeper\Test\Document\Domain\Entity\VectorDocumentBuilder;
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
        $queryService = $this->createMock(QueryService::class);
        $queryService->expects($this->exactly(2))
            ->method('query')
            ->willReturnCallback(
                static function (QueryParameters $query) {
                    if ($query instanceof FindAllDocuments) {
                        return [(new DocumentBuilder())->build()];
                    }

                    if ($query instanceof FindVectorsOfDocument) {
                        return [];
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
            ->willReturn(['foo']);

        $bus = $this->createMock(MessageBusInterface::class);
        $bus->expects($this->once())
            ->method('dispatch')
            ->willReturnCallback(
                static function (StoreDocumentVectors $command) {
                    self::assertSame([10.12], $command->vectorDocument->vector);

                    return new Envelope($command);
                },
            );

        $updater = new LibraryDocumentUpdater(new NullLogger(), $embeddingCalculator, $queryService, $bus);
        $updater->updateAll();
    }

    #[Test]
    public function itDoesNothingForExistingStorageOnUnchangedContentHash(): void
    {
        $queryService = $this->createMock(QueryService::class);
        $queryService->expects($this->exactly(2))
            ->method('query')
            ->willReturnCallback(
                static function (QueryParameters $query) {
                    if ($query instanceof FindAllDocuments) {
                        return [(new DocumentBuilder())->withContent('foo')->build()];
                    }

                    if ($query instanceof FindVectorsOfDocument) {
                        return [
                            (new VectorDocumentBuilder())
                                ->withVectorContentHash('0beec7b5ea3f0fdbc95d0dd47f3c5bc275da8a33')
                                ->build(),
                        ];
                    }

                    throw new InvalidArgumentException('Unexpected query');
                },
            );

        $embeddingCalculator = $this->createMock(EmbeddingCalculator::class);
        $embeddingCalculator->expects($this->never())->method('getSingleEmbedding');

        $bus = $this->createMock(MessageBusInterface::class);
        $bus->expects($this->never())->method('dispatch');

        $updater = new LibraryDocumentUpdater(new NullLogger(), $embeddingCalculator, $queryService, $bus);
        $updater->updateAll();
    }

    #[Test]
    public function itCleansTheVectorStorageAndRegeneratesIt(): void
    {
        $document = (new DocumentBuilder())->withId('8a998dc1-31bc-4903-8e60-6ad3232f819b')->build();

        $queryService = $this->createMock(QueryService::class);
        $queryService->expects($this->exactly(2))
            ->method('query')
            ->willReturnCallback(
                static function (QueryParameters $query) use ($document) {
                    if ($query instanceof FindAllDocuments) {
                        return [$document];
                    }

                    if ($query instanceof FindVectorsOfDocument) {
                        return [
                            (new VectorDocumentBuilder())
                                ->withId('3d9de4e8-cff0-4708-877e-03637433fd18')
                                ->withDocument($document)
                                ->withVectorContentHash('12345')
                                ->build(),
                        ];
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
            ->willReturn([$document->content]);

        $bus = $this->createMock(MessageBusInterface::class);
        $bus->expects($this->exactly(2))
            ->method('dispatch')
            ->willReturnCallback(
                static function (object $command) {
                    if ($command instanceof DeleteDocumentVectors) {
                        self::assertSame('3d9de4e8-cff0-4708-877e-03637433fd18', $command->id);
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
        $queryService->expects($this->exactly(2))
            ->method('query')
            ->willReturnCallback(
                static function (QueryParameters $query) {
                    if ($query instanceof FindAllDocuments) {
                        return [(new DocumentBuilder())->withContent('Foo Bar Baz')->build()];
                    }

                    if ($query instanceof FindVectorsOfDocument) {
                        return [];
                    }

                    throw new InvalidArgumentException('Unexpected query');
                },
            );

        $embeddingCalculator = $this->createMock(EmbeddingCalculator::class);
        $embeddingCalculator->expects($this->once())
            ->method('createTextChunks')
            ->willReturn(['Foo', 'Bar', 'Baz']);
        $embeddingCalculator->expects($this->once())
            ->method('getMultipleEmbeddings')
            ->willReturn([[10.12], [11.13], [12.14]]);

        $busInvoker = $this->exactly(3);

        $bus = $this->createMock(MessageBusInterface::class);
        $bus->expects($busInvoker)
            ->method('dispatch')
            ->willReturnCallback(
                static function (StoreDocumentVectors $command) use ($busInvoker) {
                    if ($busInvoker->numberOfInvocations() === 1) {
                        self::assertSame([10.12], $command->vectorDocument->vector);
                        self::assertSame('Foo', $command->vectorDocument->content);
                    }

                    if ($busInvoker->numberOfInvocations() === 2) {
                        self::assertSame([11.13], $command->vectorDocument->vector);
                        self::assertSame('Bar', $command->vectorDocument->content);
                    }

                    if ($busInvoker->numberOfInvocations() === 3) {
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
