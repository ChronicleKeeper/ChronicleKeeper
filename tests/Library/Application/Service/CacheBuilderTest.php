<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Library\Application\Service;

use ChronicleKeeper\Chat\Application\Query\FindConversationsByDirectoryParameters;
use ChronicleKeeper\Document\Application\Query\FindDocumentsByDirectory;
use ChronicleKeeper\Image\Application\Query\FindImagesByDirectory;
use ChronicleKeeper\Library\Application\Query\FindDirectoriesByParent;
use ChronicleKeeper\Library\Application\Service\CacheBuilder;
use ChronicleKeeper\Shared\Application\Query\QueryParameters;
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
    private QueryService&MockObject $queryService;
    private CacheBuilder $cacheBuilder;

    protected function setUp(): void
    {
        $this->queryService = $this->createMock(QueryService::class);
        $this->cacheBuilder = new CacheBuilder($this->queryService);
    }

    protected function tearDown(): void
    {
        unset($this->queryService, $this->cacheBuilder);
    }

    #[Test]
    public function buildCache(): void
    {
        $directory      = (new DirectoryBuilder())->build();
        $childDirectory = (new DirectoryBuilder())->withParent($directory)->build();
        $document       = (new DocumentBuilder())->withDirectory($directory)->build();
        $image          = (new ImageBuilder())->withDirectory($directory)->build();
        $conversation   = (new ConversationBuilder())->withDirectory($directory)->build();

        $this->queryService->expects($this->exactly(4))
            ->method('query')
            ->willReturnCallback(
                static function (QueryParameters $query) use ($document, $conversation, $childDirectory, $image) {
                    if ($query instanceof FindDocumentsByDirectory) {
                        self::assertSame($document->getDirectory()->getId(), $query->id);

                        return [$document];
                    }

                    if ($query instanceof FindConversationsByDirectoryParameters) {
                        self::assertSame($conversation->getDirectory()->getId(), $query->directory->getId());

                        return [$conversation];
                    }

                    if ($query instanceof FindDirectoriesByParent) {
                        self::assertSame($document->getDirectory()->getId(), $query->parentId);

                        return [$childDirectory];
                    }

                    if ($query instanceof FindImagesByDirectory) {
                        self::assertSame($image->getDirectory()->getId(), $query->id);

                        return [$image];
                    }

                    self::fail('Unexpected query executed: ' . $query::class);
                },
            );

        $cacheDirectory = $this->cacheBuilder->build($directory);

        self::assertCount(1, $cacheDirectory->directories);
        self::assertCount(3, $cacheDirectory->elements);
    }
}
