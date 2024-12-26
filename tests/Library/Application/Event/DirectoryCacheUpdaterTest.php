<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Library\Application\Event;

use ChronicleKeeper\Chat\Domain\Event\ConversationCreated;
use ChronicleKeeper\Chat\Domain\Event\ConversationDeleted;
use ChronicleKeeper\Chat\Domain\Event\ConversationMovedToDirectory;
use ChronicleKeeper\Chat\Domain\Event\ConversationRenamed;
use ChronicleKeeper\Document\Domain\Event\DocumentCreated;
use ChronicleKeeper\Document\Domain\Event\DocumentDeleted;
use ChronicleKeeper\Document\Domain\Event\DocumentMovedToDirectory;
use ChronicleKeeper\Document\Domain\Event\DocumentRenamed;
use ChronicleKeeper\Image\Domain\Event\ImageCreated;
use ChronicleKeeper\Image\Domain\Event\ImageDeleted;
use ChronicleKeeper\Image\Domain\Event\ImageMovedToDirectory;
use ChronicleKeeper\Image\Domain\Event\ImageRenamed;
use ChronicleKeeper\Library\Application\Event\DirectoryCacheUpdater;
use ChronicleKeeper\Library\Application\Service\CacheReader;
use ChronicleKeeper\Library\Domain\Entity\Directory;
use ChronicleKeeper\Library\Domain\Event\DirectoryDeleted;
use ChronicleKeeper\Library\Domain\Event\DirectoryMovedToDirectory;
use ChronicleKeeper\Library\Domain\Event\DirectoryRenamed;
use ChronicleKeeper\Library\Domain\RootDirectory;
use ChronicleKeeper\Library\Domain\ValueObject\DirectoryCache\Directory as DirectoryCache;
use ChronicleKeeper\Test\Chat\Domain\Entity\ConversationBuilder;
use ChronicleKeeper\Test\Document\Domain\Entity\DocumentBuilder;
use ChronicleKeeper\Test\Image\Domain\Entity\ImageBuilder;
use ChronicleKeeper\Test\Library\Domain\Entity\DirectoryBuilder;
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
    public function updateOnImageMovedToDirectory(): void
    {
        $directory    = (new DirectoryBuilder())->build();
        $oldDirectory = (new DirectoryBuilder())->build();
        $image        = (new ImageBuilder())->withDirectory($directory)->build();

        $event = new ImageMovedToDirectory($image, $oldDirectory);

        $invoker = $this->exactly(2);
        $this->cacheReader->expects($invoker)
            ->method('refresh')
            ->willReturnCallback(
                static function (Directory $argDirectory) use ($invoker, $directory, $oldDirectory): DirectoryCache {
                    if ($invoker->numberOfInvocations() === 1) {
                        self::assertSame($directory, $argDirectory);

                        return DirectoryCache::fromEntity($directory);
                    }

                    self::assertSame($oldDirectory, $argDirectory);

                    return DirectoryCache::fromEntity($directory);
                },
            );

        $this->directoryCacheUpdater->updateOnImageMovedToDirectory($event);
    }

    #[Test]
    public function updateOnImageRenamed(): void
    {
        $directory = (new DirectoryBuilder())->build();
        $image     = (new ImageBuilder())->withDirectory($directory)->build();

        $event = new ImageRenamed($image, 'new-name');

        $this->cacheReader->expects($this->once())
            ->method('refresh')
            ->with($directory);

        $this->directoryCacheUpdater->updateOnImageRenamed($event);
    }

    #[Test]
    public function updateOnImageCreated(): void
    {
        $directory = (new DirectoryBuilder())->build();
        $image     = (new ImageBuilder())->withDirectory($directory)->build();

        $event = new ImageCreated($image);

        $this->cacheReader->expects($this->once())
            ->method('refresh')
            ->with($directory);

        $this->directoryCacheUpdater->updateOnImageCreated($event);
    }

    #[Test]
    public function updateOnDocumentCreated(): void
    {
        $directory = (new DirectoryBuilder())->build();
        $document  = (new DocumentBuilder())->withDirectory($directory)->build();

        $event = new DocumentCreated($document);

        $this->cacheReader->expects($this->once())
            ->method('refresh')
            ->with($directory);

        $this->directoryCacheUpdater->updateOnDocumentCreated($event);
    }

    #[Test]
    public function updateOnDocumentMovedToDirectory(): void
    {
        $directory    = (new DirectoryBuilder())->build();
        $oldDirectory = (new DirectoryBuilder())->build();
        $document     = (new DocumentBuilder())->withDirectory($directory)->build();

        $event = new DocumentMovedToDirectory($document, $oldDirectory);

        $invoker = $this->exactly(2);
        $this->cacheReader->expects($invoker)
            ->method('refresh')
            ->willReturnCallback(
                static function (Directory $argDirectory) use ($invoker, $directory, $oldDirectory): DirectoryCache {
                    if ($invoker->numberOfInvocations() === 1) {
                        self::assertSame($directory, $argDirectory);

                        return DirectoryCache::fromEntity($directory);
                    }

                    self::assertSame($oldDirectory, $argDirectory);

                    return DirectoryCache::fromEntity($directory);
                },
            );

        $this->directoryCacheUpdater->updateOnDocumentMovedToDirectory($event);
    }

    #[Test]
    public function updateOnDocumentRenamed(): void
    {
        $directory = (new DirectoryBuilder())->build();
        $document  = (new DocumentBuilder())->withDirectory($directory)->build();

        $event = new DocumentRenamed($document, 'new-name');

        $this->cacheReader->expects($this->once())
            ->method('refresh')
            ->with($directory);

        $this->directoryCacheUpdater->updateOnDocumentRenamed($event);
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

    #[Test]
    public function updateOnConversationMovedToDirectory(): void
    {
        $directory    = (new DirectoryBuilder())->build();
        $oldDirectory = (new DirectoryBuilder())->build();
        $conversation = (new ConversationBuilder())->withDirectory($directory)->build();

        $event = new ConversationMovedToDirectory($conversation, $oldDirectory);

        $invoker = $this->exactly(2);
        $this->cacheReader->expects($invoker)
            ->method('refresh')
            ->willReturnCallback(
                static function (Directory $argDirectory) use ($invoker, $directory, $oldDirectory): DirectoryCache {
                    if ($invoker->numberOfInvocations() === 1) {
                        self::assertSame($directory, $argDirectory);

                        return DirectoryCache::fromEntity($directory);
                    }

                    self::assertSame($oldDirectory, $argDirectory);

                    return DirectoryCache::fromEntity($directory);
                },
            );

        $this->directoryCacheUpdater->updateOnConversationMovedToDirectory($event);
    }

    #[Test]
    public function updateOnConversationRenamed(): void
    {
        $directory    = (new DirectoryBuilder())->build();
        $conversation = (new ConversationBuilder())->withDirectory($directory)->build();

        $event = new ConversationRenamed($conversation, 'new-name');

        $this->cacheReader->expects($this->once())
            ->method('refresh')
            ->with($directory);

        $this->directoryCacheUpdater->updateOnConversationRenamed($event);
    }

    #[Test]
    public function updateOnConversationCreated(): void
    {
        $directory    = (new DirectoryBuilder())->build();
        $conversation = (new ConversationBuilder())->withDirectory($directory)->build();

        $event = new ConversationCreated($conversation);

        $this->cacheReader->expects($this->once())
            ->method('refresh')
            ->with($directory);

        $this->directoryCacheUpdater->updateOnConversationCreated($event);
    }

    #[Test]
    public function updateOnDirectoryDeleted(): void
    {
        $parentDirectory = (new DirectoryBuilder())->build();
        $directory       = (new DirectoryBuilder())->withParent($parentDirectory)->build();

        $event = new DirectoryDeleted($directory);

        $this->cacheReader->expects($this->once())
            ->method('refresh')
            ->with($parentDirectory);

        $this->cacheReader->expects($this->once())
            ->method('remove')
            ->with($directory);

        $this->directoryCacheUpdater->updateOnDirectoryDeleted($event);
    }

    #[Test]
    public function updateOnDirectoryDeletedWhenParentDirectoryIsNull(): void
    {
        $directory = RootDirectory::get();

        $event = new DirectoryDeleted($directory);

        $this->cacheReader->expects($this->never())
            ->method('refresh');

        $this->cacheReader->expects($this->once())
            ->method('remove')
            ->with($directory);

        $this->directoryCacheUpdater->updateOnDirectoryDeleted($event);
    }

    #[Test]
    public function updateOnDirectoryMovedToDirectory(): void
    {
        $directory    = (new DirectoryBuilder())->build();
        $oldDirectory = (new DirectoryBuilder())->build();

        $event = new DirectoryMovedToDirectory($directory, $oldDirectory);

        $invoker = $this->exactly(2);
        $this->cacheReader->expects($invoker)
            ->method('refresh')
            ->willReturnCallback(
                static function (Directory $argDirectory) use ($invoker, $directory, $oldDirectory): DirectoryCache {
                    if ($invoker->numberOfInvocations() === 1) {
                        self::assertSame($directory->getParent(), $argDirectory);

                        return DirectoryCache::fromEntity($directory);
                    }

                    self::assertSame($oldDirectory, $argDirectory);

                    return DirectoryCache::fromEntity($directory);
                },
            );

        $this->directoryCacheUpdater->updateOnDirectoryMovedToDirectory($event);
    }

    #[Test]
    public function updateOnDirectoryRenamed(): void
    {
        $directory = (new DirectoryBuilder())->build();

        $event = new DirectoryRenamed($directory, 'new-name');

        $this->cacheReader->expects($this->once())
            ->method('refresh')
            ->with($directory);

        $this->directoryCacheUpdater->updateOnDirectoryRenamed($event);
    }
}
