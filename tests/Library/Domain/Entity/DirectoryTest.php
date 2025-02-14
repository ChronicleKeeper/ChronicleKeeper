<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Library\Domain\Entity;

use ChronicleKeeper\Library\Domain\Entity\Directory;
use ChronicleKeeper\Library\Domain\Event\DirectoryCreated;
use ChronicleKeeper\Library\Domain\Event\DirectoryMovedToDirectory;
use ChronicleKeeper\Library\Domain\Event\DirectoryRenamed;
use ChronicleKeeper\Library\Domain\RootDirectory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

use function json_encode;

use const JSON_THROW_ON_ERROR;

#[CoversClass(Directory::class)]
#[Small]
final class DirectoryTest extends TestCase
{
    #[Test]
    public function itCanBeConstructedWithoutOptionalArguments(): void
    {
        $directory = new Directory('id', 'title');

        self::assertSame('id', $directory->getId());
        self::assertSame('title', $directory->getTitle());
        self::assertNull($directory->getParent());
    }

    #[Test]
    public function itCanBeConstructedWithAllArguments(): void
    {
        $parent    = (new DirectoryBuilder())->build();
        $directory = new Directory('id', 'title', $parent);

        self::assertSame('id', $directory->getId());
        self::assertSame('title', $directory->getTitle());
        self::assertSame($parent, $directory->getParent());
    }

    #[Test]
    public function itWillBeCreatedWithRootDirectoryWhenNoParent(): void
    {
        $directory = Directory::create('title');

        self::assertSame('title', $directory->getTitle());
        self::assertNotNull($directory->getParent());
        self::assertTrue($directory->getParent()->equals(RootDirectory::get()));

        // Test event recording
        $events = $directory->flushEvents();
        self::assertCount(1, $events);
        self::assertInstanceOf(DirectoryCreated::class, $events[0]);
    }

    #[Test]
    public function itCanBeCreatedWithParent(): void
    {
        $parent    = (new DirectoryBuilder())->build();
        $directory = Directory::create('title', $parent);

        self::assertSame('title', $directory->getTitle());
        self::assertSame($parent, $directory->getParent());

        // Test event recording
        $events = $directory->flushEvents();
        self::assertCount(1, $events);
        self::assertInstanceOf(DirectoryCreated::class, $events[0]);
    }

    #[Test]
    public function itCanBeJsonSerialized(): void
    {
        $directory = new Directory('id', 'title');

        self::assertSame(
            '{"id":"id","title":"title","parent":"caf93493-9072-44e2-a6db-4476985a849d"}',
            json_encode($directory, JSON_THROW_ON_ERROR),
        );
    }

    #[Test]
    public function itCanBeRenamed(): void
    {
        $directory = new Directory('id', 'title');
        $directory->rename('new title');

        self::assertSame('new title', $directory->getTitle());

        // Test event recording
        $events = $directory->flushEvents();
        self::assertCount(1, $events);
        self::assertInstanceOf(DirectoryRenamed::class, $events[0]);
    }

    #[Test]
    public function itHappensNothingWhenRenamingWithSameTitle(): void
    {
        $directory = new Directory('id', 'title');
        $directory->rename('title');

        self::assertSame('title', $directory->getTitle());

        // Test event recording
        $events = $directory->flushEvents();
        self::assertEmpty($events);
    }

    #[Test]
    public function itCanBeMovedToAnotherDirectory(): void
    {
        $parent    = (new DirectoryBuilder())->build();
        $directory = (new DirectoryBuilder())->build();
        $directory->moveToDirectory($parent);

        self::assertSame($parent, $directory->getParent());

        // Test event recording
        $events = $directory->flushEvents();
        self::assertCount(1, $events);
        self::assertInstanceOf(DirectoryMovedToDirectory::class, $events[0]);
    }

    #[Test]
    public function itHappensNothingWhenMovingToSameDirectory(): void
    {
        $parent    = (new DirectoryBuilder())->build();
        $directory = (new DirectoryBuilder())->withParent($parent)->build();
        $directory->moveToDirectory($parent);

        self::assertSame($parent, $directory->getParent());

        // Test event recording
        $events = $directory->flushEvents();
        self::assertEmpty($events);
    }

    #[Test]
    public function itWillHappenNothingWhenMovingTheRootDirectory(): void
    {
        // The root directory is the only directoy having a null parent
        $directory = RootDirectory::get();
        $directory->moveToDirectory((new DirectoryBuilder())->build());

        self::assertNull($directory->getParent());

        // Test event recording
        $events = $directory->flushEvents();
        self::assertEmpty($events);
    }

    #[Test]
    public function itCanCheckTheEqualityOfDirectory(): void
    {
        $directory1 = new Directory('id', 'title');
        $directory2 = new Directory('id', 'title');
        $directory3 = new Directory('another-id', 'another title');

        self::assertTrue($directory1->equals($directory2));
        self::assertFalse($directory1->equals($directory3));
    }

    #[Test]
    public function itGiveAFlattenStringTitleRepresentingTheDirectoryPath(): void
    {
        $firstDirectory  = (new DirectoryBuilder())->withTitle('first')->build();
        $secondDirectory = (new DirectoryBuilder())->withTitle('second')->withParent($firstDirectory)->build();

        self::assertSame('Hauptverzeichnis > first > second', $secondDirectory->flattenHierarchyTitle());
    }

    #[Test]
    public function itCanCheckIfItIsTheRootDirectory(): void
    {
        $directory = new Directory('id', 'title');
        self::assertFalse($directory->isRoot());

        $rootDirectory = RootDirectory::get();
        self::assertTrue($rootDirectory->isRoot());
    }

    #[Test]
    public function itCanBeConvertedToAnArray(): void
    {
        $directory = new Directory('id', 'title');

        self::assertSame(
            [
                'id'     => 'id',
                'title'  => 'title',
                'parent' => 'caf93493-9072-44e2-a6db-4476985a849d',
            ],
            $directory->toArray(),
        );
    }
}
