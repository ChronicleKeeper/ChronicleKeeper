<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Shared\Infrastructure\Persistence\Filesystem;

use ChronicleKeeper\Shared\Infrastructure\Persistence\Filesystem\Contracts\Finder;
use PHPUnit\Framework\MockObject\Generator\Generator as MockGenerator;
use SplFileInfo;
use Symfony\Contracts\Service\ResetInterface;

use function array_keys;
use function array_map;
use function assert;

class FinderDouble implements Finder, ResetInterface
{
    /** @var array<string, array<string, string>> */
    private array $storage = [];

    public function addFile(string $directory, string $filename, string $content): void
    {
        $this->storage[$directory][$filename] = $content;
    }

    /** @return iterable<SplFileInfo> */
    public function findFilesInDirectory(string $directory, bool $withDotFiles = true): iterable
    {
        return $this->convertDirectoryToFileList($directory);
    }

    /** @return iterable<SplFileInfo> */
    public function findFilesInDirectoryOrderedByAccessTimestamp(string $directory, bool $withDotFiles = true): iterable
    {
        return $this->convertDirectoryToFileList($directory);
    }

    public function reset(): void
    {
        $this->storage = [];
    }

    /** @return iterable<SplFileInfo> */
    private function convertDirectoryToFileList(string $directory): iterable
    {
        $files = $this->storage[$directory] ?? [];

        return array_map(
            fn (string $filename, string $content) => $this->convertToSplFileInfoMock($filename, $content),
            array_keys($files),
            $files,
        );
    }

    private function convertToSplFileInfoMock(string $filename, string $content): SplFileInfo
    {
        $stub = (new MockGenerator())
            ->testDouble(
                SplFileInfo::class,
                true,
                false,
                callOriginalConstructor: false,
                callOriginalClone: false,
                cloneArguments: false,
                allowMockingUnknownTypes: false,
            );

        $stub->method('getFilename')->willReturn($filename);
        $stub->method('getContents')->willReturn($content);

        assert($stub instanceof SplFileInfo);

        return $stub;
    }
}
