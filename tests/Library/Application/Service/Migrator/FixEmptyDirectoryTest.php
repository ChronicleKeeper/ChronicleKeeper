<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Library\Application\Service\Migrator;

use ChronicleKeeper\Library\Application\Service\Migrator\FixEmptyDirectory;
use ChronicleKeeper\Settings\Application\Service\FileType;
use ChronicleKeeper\Shared\Infrastructure\Persistence\Filesystem\Contracts\FileAccess;
use Generator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(FixEmptyDirectory::class)]
#[Small]
class FixEmptyDirectoryTest extends TestCase
{
    #[Test]
    #[DataProvider('provideSupportingTests')]
    public function migratorSupportsCorrectFiletypes(FileType $type, bool $expected): void
    {
        $migrator = new FixEmptyDirectory(self::createStub(FileAccess::class));
        $return   = $migrator->isSupporting($type, 'dev');

        if ($expected === true) {
            self::assertTrue($return);

            return;
        }

        self::assertFalse($return);
    }

    public static function provideSupportingTests(): Generator
    {
        yield 'Settings not supported' => [FileType::SETTINGS, false];
        yield 'Vector Documents not supported' => [FileType::VECTOR_STORAGE_DOCUMENT, false];
        yield 'Documents supported' => [FileType::LIBRARY_DOCUMENT, true];
        yield 'Images supported' => [FileType::LIBRARY_IMAGE, true];
        yield 'Directories not supported' => [FileType::LIBRARY_DIRECTORY, false];
    }

    #[Test]
    public function theFileIsNotOverwrittenWhenDirectoryAlreadyInThere(): void
    {
        $filesystem = $this->createMock(FileAccess::class);
        $filesystem->expects($this->once())
            ->method('read')
            ->with('library.documents', 'foo')
            ->willReturn('{"directory": "123-123-123-123"}');

        $filesystem->expects($this->never())->method('write');

        $migrator = new FixEmptyDirectory($filesystem);
        $migrator->migrate('foo', FileType::LIBRARY_DOCUMENT);
    }

    #[Test]
    public function theRootDirectoryIsAddedToTheDocumentWhenNoDirectoryInThere(): void
    {
        $filesystem = $this->createMock(FileAccess::class);
        $filesystem->expects($this->once())
            ->method('read')
            ->with('library.documents', 'foo')
            ->willReturn('{}');

        $filesystem->expects($this->once())
            ->method('write')
            ->with('library.documents', 'foo', "{\n    \"directory\": \"caf93493-9072-44e2-a6db-4476985a849d\"\n}");

        $migrator = new FixEmptyDirectory($filesystem);
        $migrator->migrate('foo', FileType::LIBRARY_DOCUMENT);
    }
}
