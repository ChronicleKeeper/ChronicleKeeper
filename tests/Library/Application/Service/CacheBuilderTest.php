<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Library\Application\Service;

use ChronicleKeeper\Chat\Application\Query\FindConversationsByDirectoryParameters;
use ChronicleKeeper\Document\Application\Query\FindDocumentsByDirectory;
use ChronicleKeeper\Library\Application\Service\CacheBuilder;
use ChronicleKeeper\Library\Infrastructure\Repository\FilesystemDirectoryRepository;
use ChronicleKeeper\Library\Infrastructure\Repository\FilesystemImageRepository;
use ChronicleKeeper\Shared\Application\Query\QueryService;
use ChronicleKeeper\Test\Chat\Domain\Entity\ConversationBuilder;
use ChronicleKeeper\Test\Document\Domain\Entity\DocumentBuilder;
use ChronicleKeeper\Test\Image\Domain\Entity\ImageBuilder;
use ChronicleKeeper\Test\Library\Domain\Entity\DirectoryBuilder;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

#[CoversClass(CacheBuilder::class)]
#[Small]
class CacheBuilderTest extends TestCase
{
    private FilesystemImageRepository&MockObject $imageRepository;
    private FilesystemDirectoryRepository&MockObject $directoryRepository;
    private QueryService&MockObject $queryService;
    private CacheBuilder $cacheBuilder;

    protected function setUp(): void
    {
        $this->imageRepository     = $this->createMock(FilesystemImageRepository::class);
        $this->directoryRepository = $this->createMock(FilesystemDirectoryRepository::class);
        $this->queryService        = $this->createMock(QueryService::class);
        $this->cacheBuilder        = new CacheBuilder(
            $this->imageRepository,
            $this->directoryRepository,
            $this->queryService,
        );
    }

    protected function tearDown(): void
    {
        unset($this->imageRepository, $this->directoryRepository, $this->queryService, $this->cacheBuilder);
    }

    #[Test]
    public function buildCache(): void
    {
        $directory      = (new DirectoryBuilder())->build();
        $childDirectory = (new DirectoryBuilder())->withParent($directory)->build();
        $document       = (new DocumentBuilder())->withDirectory($directory)->build();
        $image          = (new ImageBuilder())->withDirectory($directory)->build();
        $conversation   = (new ConversationBuilder())->withDirectory($directory)->build();

        $this->directoryRepository->expects($this->once())
            ->method('findByParent')
            ->with($directory)
            ->willReturn([$childDirectory]);

        $this->queryService->expects($this->exactly(2))
            ->method('query')
            ->willReturnCallback(
                static function (FindDocumentsByDirectory|FindConversationsByDirectoryParameters $query) use ($document, $conversation) {
                    if ($query instanceof FindDocumentsByDirectory) {
                        self::assertSame($document->getDirectory()->getId(), $query->id);

                        return [$document];
                    }

                    self::assertSame($conversation->getDirectory()->getId(), $query->directory->getId());

                    return [$conversation];
                },
            );

        $this->imageRepository->expects($this->once())
            ->method('findByDirectory')
            ->with($directory)
            ->willReturn([$image]);

        $cacheDirectory = $this->cacheBuilder->build($directory);

        self::assertCount(1, $cacheDirectory->directories);
        self::assertCount(3, $cacheDirectory->elements);
    }
}
