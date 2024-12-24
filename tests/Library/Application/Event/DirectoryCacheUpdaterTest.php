<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Library\Application\Event;

use ChronicleKeeper\Chat\Domain\Event\ConversationDeleted;
use ChronicleKeeper\Document\Domain\Event\DocumentDeleted;
use ChronicleKeeper\Library\Application\Event\DirectoryCacheUpdater;
use ChronicleKeeper\Library\Application\Service\CacheReader;
use ChronicleKeeper\Library\Domain\Event\ImageDeleted;
use ChronicleKeeper\Test\Chat\Domain\Entity\ConversationBuilder;
use ChronicleKeeper\Test\Document\Domain\Entity\DocumentBuilder;
use ChronicleKeeper\Test\Library\Domain\Entity\DirectoryBuilder;
use ChronicleKeeper\Test\Library\Domain\Entity\ImageBuilder;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

#[CoversClass(DirectoryCacheUpdater::class)]
#[Small]
class DirectoryCacheUpdaterTest extends TestCase
{
    private CacheReader&MockObject $cacheReader;
    private DirectoryCacheUpdater $directoryCacheUpdater;

    protected function setUp(): void
    {
        $this->cacheReader           = $this->createMock(CacheReader::class);
        $this->directoryCacheUpdater = new DirectoryCacheUpdater($this->cacheReader);
    }

    public function tearDown(): void
    {
        unset($this->cacheReader, $this->directoryCacheUpdater);
    }

    #[Test]
    public function updateOnImageDeleted(): void
    {
        $directory = (new DirectoryBuilder())->build();
        $image     = (new ImageBuilder())->withDirectory($directory)->build();

        $event = new ImageDeleted($image);

        $this->cacheReader->expects($this->once())
            ->method('refresh')
            ->with($directory);

        $this->directoryCacheUpdater->updateOnImageDeleted($event);
    }

    #[Test]
    public function updateOnDocumentDeleted(): void
    {
        $directory = (new DirectoryBuilder())->build();
        $document  = (new DocumentBuilder())->withDirectory($directory)->build();

        $event = new DocumentDeleted($document);

        $this->cacheReader->expects($this->once())
            ->method('refresh')
            ->with($directory);

        $this->directoryCacheUpdater->updateOnDocumentDeleted($event);
    }

    #[Test]
    public function updateOnConversationDeleted(): void
    {
        $directory    = (new DirectoryBuilder())->build();
        $conversation = (new ConversationBuilder())->withDirectory($directory)->build();

        $event = new ConversationDeleted($conversation);

        $this->cacheReader->expects($this->once())
            ->method('refresh')
            ->with($directory);

        $this->directoryCacheUpdater->updateOnConversationDeleted($event);
    }
}
