<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Library\Domain\ValueObject\DirectoryContent;

use ChronicleKeeper\Library\Domain\Entity\Directory as DirectoryEntity;
use ChronicleKeeper\Library\Domain\ValueObject\DirectoryContent\Directory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(Directory::class)]
#[Small]
class DirectoryTest extends TestCase
{
    #[Test]
    public function itCanBeConstructed(): void
    {
        $directory = new Directory('id', 'title', [], []);

        self::assertSame('id', $directory->id);
        self::assertSame('title', $directory->title);
        self::assertSame([], $directory->elements);
        self::assertSame([], $directory->directories);
    }

    #[Test]
    public function itCanBeCreatedFromEntity(): void
    {
        $directoryEntity = $this->createMock(DirectoryEntity::class);
        $directoryEntity->method('getId')->willReturn('id');
        $directoryEntity->method('getTitle')->willReturn('title');

        $directory = Directory::fromEntity($directoryEntity);

        self::assertSame('id', $directory->id);
        self::assertSame('title', $directory->title);
        self::assertSame([], $directory->elements);
        self::assertSame([], $directory->directories);
    }
}
