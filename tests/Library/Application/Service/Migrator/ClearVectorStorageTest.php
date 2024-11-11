<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Library\Application\Service\Migrator;

use ChronicleKeeper\Library\Application\Service\Migrator\ClearVectorStorage;
use ChronicleKeeper\Settings\Application\Service\FileType;
use ChronicleKeeper\Shared\Infrastructure\Persistence\Filesystem\Contracts\FileAccess;
use Generator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(ClearVectorStorage::class)]
#[Small]
class ClearVectorStorageTest extends TestCase
{
    #[Test]
    #[DataProvider('provideSupporting')]
    public function isSupporting(FileType $type, string $version, bool $xpectedResult): void
    {
        $migrator = new ClearVectorStorage(self::createStub(FileAccess::class));
        self::assertSame($xpectedResult, $migrator->isSupporting($type, $version));
    }

    public static function provideSupporting(): Generator
    {
        yield 'image vector of 0.4' => [FileType::VECTOR_STORAGE_IMAGE, '0.4', true];
        yield 'document vector of 0.4' => [FileType::VECTOR_STORAGE_DOCUMENT, '0.4', true];
        yield 'image vector of 0.5' => [FileType::VECTOR_STORAGE_IMAGE, '0.5', false];
        yield 'document vector of 0.5' => [FileType::VECTOR_STORAGE_DOCUMENT, '0.5', false];
        yield 'image vector of 0.6' => [FileType::VECTOR_STORAGE_IMAGE, '0.6', false];
        yield 'document vector of 0.6' => [FileType::VECTOR_STORAGE_DOCUMENT, '0.6', false];
    }

    #[Test]
    public function migrateDocument(): void
    {
        $fileAccess = $this->createMock(FileAccess::class);
        $fileAccess->expects($this->once())
            ->method('delete')
            ->with('vector.documents', 'file');

        $migrator = new ClearVectorStorage($fileAccess);
        $migrator->migrate('file', FileType::VECTOR_STORAGE_DOCUMENT);
    }

    #[Test]
    public function migrateImage(): void
    {
        $fileAccess = $this->createMock(FileAccess::class);
        $fileAccess->expects($this->once())
            ->method('delete')
            ->with('vector.images', 'file');

        $migrator = new ClearVectorStorage($fileAccess);
        $migrator->migrate('file', FileType::VECTOR_STORAGE_IMAGE);
    }
}
