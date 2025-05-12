<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Image\Infrastructure\VectorStorage;

use ChronicleKeeper\Image\Application\Command\DeleteImageVectors;
use ChronicleKeeper\Image\Application\Query\FindAllImages;
use ChronicleKeeper\Image\Infrastructure\VectorStorage\LibraryImageUpdater;
use ChronicleKeeper\Shared\Application\Query\QueryParameters;
use ChronicleKeeper\Shared\Application\Query\QueryService;
use ChronicleKeeper\Shared\Infrastructure\LLMChain\EmbeddingCalculator;
use ChronicleKeeper\Shared\Infrastructure\LLMChain\Exception\EmbeddingCalculationFailed;
use ChronicleKeeper\Test\Image\Domain\Entity\ImageBuilder;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;

#[CoversClass(LibraryImageUpdater::class)]
#[Small]
final class LibraryImageUpdaterTest extends TestCase
{
    #[Test]
    public function itDoesNothingWhenThereAreNoImages(): void
    {
        $queryService = $this->createMock(QueryService::class);
        $queryService->expects($this->once())
            ->method('query')
            ->with(self::isInstanceOf(FindAllImages::class))
            ->willReturn([]);

        $embeddingCalculator = $this->createMock(EmbeddingCalculator::class);
        $embeddingCalculator->expects($this->never())->method('createTextChunks');
        $embeddingCalculator->expects($this->never())->method('getMultipleEmbeddings');
        $embeddingCalculator->expects($this->never())->method('getMultipleEmbeddings');

        $bus = $this->createMock(MessageBusInterface::class);
        $bus->expects($this->never())->method('dispatch');

        $updater = new LibraryImageUpdater(new NullLogger(), $embeddingCalculator, $queryService, $bus);
        $updater->updateAll();
    }

    #[Test]
    public function itCanHandleAnImageWithoutAnyContent(): void
    {
        $image = (new ImageBuilder())
            ->withId('8a998dc1-31bc-4903-8e60-6ad3232f819b')
            ->withDescription('')
            ->build();

        $queryService = $this->createMock(QueryService::class);
        $queryService->expects($this->once())
            ->method('query')
            ->willReturnCallback(
                static function (QueryParameters $query) use ($image) {
                    if ($query instanceof FindAllImages) {
                        return [$image];
                    }

                    throw new InvalidArgumentException('Unexpected query');
                },
            );

        $embeddingCalculator = $this->createMock(EmbeddingCalculator::class);
        $embeddingCalculator->expects($this->never())->method('getSingleEmbedding');
        $embeddingCalculator->expects($this->never())->method('getMultipleEmbeddings')->willReturn([]);
        $embeddingCalculator->expects($this->once())->method('createTextChunks')->willReturn([]);

        $bus = $this->createMock(MessageBusInterface::class);
        $bus->expects($this->once())
            ->method('dispatch')
            ->willReturnCallback(
                static function (object $command) use ($image) {
                    if ($command instanceof DeleteImageVectors) {
                        self::assertSame($image->getId(), $command->imageId);
                    }

                    return new Envelope($command);
                },
            );

        $updater = new LibraryImageUpdater(new NullLogger(), $embeddingCalculator, $queryService, $bus);
        $updater->updateAll();
    }

    #[Test]
    public function itIsCatchingAnEmbeddingExceptionAnDoesNothing(): void
    {
        $queryService = $this->createMock(QueryService::class);
        $queryService->expects($this->once())
            ->method('query')
            ->willReturnCallback(
                static function (QueryParameters $query) {
                    if ($query instanceof FindAllImages) {
                        return [(new ImageBuilder())->withDescription('Foo Bar Baz')->build()];
                    }

                    throw new InvalidArgumentException('Unexpected query ' . $query::class);
                },
            );

        $embeddingCalculator = $this->createMock(EmbeddingCalculator::class);
        $embeddingCalculator->expects($this->once())->method('createTextChunks')->willReturn(['Foo']);
        $embeddingCalculator->expects($this->once())->method('getMultipleEmbeddings')
            ->willThrowException(new EmbeddingCalculationFailed());

        $bus = $this->createMock(MessageBusInterface::class);
        $bus->expects($this->once())
            ->method('dispatch')
            ->willReturnCallback(
                static fn (DeleteImageVectors $command) => new Envelope($command),
            );

        $updater = new LibraryImageUpdater(new NullLogger(), $embeddingCalculator, $queryService, $bus);
        $updater->updateAll();
    }
}
