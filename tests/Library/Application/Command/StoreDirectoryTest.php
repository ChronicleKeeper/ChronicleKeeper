<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Library\Application\Command;

use ChronicleKeeper\Library\Application\Command\StoreDirectory;
use ChronicleKeeper\Library\Application\Command\StoreDirectoryHandler;
use ChronicleKeeper\Library\Domain\Entity\Directory;
use ChronicleKeeper\Library\Domain\Event\DirectoryCreated;
use ChronicleKeeper\Library\Domain\Event\DirectoryRenamed;
use ChronicleKeeper\Library\Domain\RootDirectory;
use ChronicleKeeper\Test\Shared\Infrastructure\Database\SQLite\DatabaseTestCase;
use InvalidArgumentException;
use Override;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Large;
use PHPUnit\Framework\Attributes\Test;

use function assert;

#[CoversClass(StoreDirectory::class)]
#[CoversClass(StoreDirectoryHandler::class)]
#[Large]
final class StoreDirectoryTest extends DatabaseTestCase
{
    private StoreDirectoryHandler $handler;

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        $handler = self::getContainer()->get(StoreDirectoryHandler::class);
        assert($handler instanceof StoreDirectoryHandler);

        $this->handler = $handler;
    }

    #[Override]
    protected function tearDown(): void
    {
        parent::tearDown();

        unset($this->handler);
    }

    #[Test]
    public function itDoesNotAllowToStoreTheRootDirectory(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The root directory can not be overwritten.');

        new StoreDirectory(RootDirectory::get());
    }

    #[Test]
    public function itIsAbleToStoreANewDirectory(): void
    {
        // ------------------- The test setup -------------------

        $directory = Directory::create('Foo', RootDirectory::get());

        // ------------------- The test scenario -------------------

        $messageResult = ($this->handler)(new StoreDirectory($directory));

        // ------------------- The test assertions -------------------

        $events = $messageResult->getEvents();
        self::assertCount(1, $events);
        self::assertInstanceOf(DirectoryCreated::class, $events[0]);
    }

    #[Test]
    public function itIsAbleToStoreAnChangedirectory(): void
    {
        // ------------------- The test setup -------------------

        $directory = Directory::create('Foo', RootDirectory::get());
        $this->bus->dispatch(new StoreDirectory($directory));

        $directory->rename('Foo Bar Baz');

        // ------------------- The test scenario -------------------

        $messageResult = ($this->handler)(new StoreDirectory($directory));

        // ------------------- The test assertions -------------------

        $events = $messageResult->getEvents();
        self::assertCount(1, $events);
        self::assertInstanceOf(DirectoryRenamed::class, $events[0]);

        $rawDirectory = $this->getRowFromTable('directories', 'id', $directory->getId());
        self::assertIsArray($rawDirectory);
        self::assertSame('Foo Bar Baz', $rawDirectory['title']);
    }
}
