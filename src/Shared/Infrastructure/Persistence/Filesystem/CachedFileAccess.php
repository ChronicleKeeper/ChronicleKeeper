<?php

declare(strict_types=1);

namespace ChronicleKeeper\Shared\Infrastructure\Persistence\Filesystem;

use ChronicleKeeper\Shared\Infrastructure\Persistence\Filesystem\Contracts\FileAccess as FileAccessContract;
use Symfony\Component\DependencyInjection\Attribute\AsAlias;
use Symfony\Component\DependencyInjection\Attribute\AsDecorator;
use Symfony\Component\DependencyInjection\Attribute\AutowireDecorated;

#[AsDecorator(decorates: FileAccess::class)]
#[AsAlias(FileAccessContract::class)]
class CachedFileAccess implements FileAccessContract
{
    /** @var array<string, array<string, string>> */
    private array $cachedFiles = [];

    public function __construct(
        #[AutowireDecorated]
        private readonly FileAccessContract $fileAccess,
    ) {
    }

    public function read(string $type, string $filename): string
    {
        if (! isset($this->cachedFiles[$type][$filename])) {
            $this->cachedFiles[$type][$filename] = $this->fileAccess->read($type, $filename);
        }

        return $this->cachedFiles[$type][$filename];
    }

    public function exists(string $type, string $filename): bool
    {
        return isset($this->cachedFiles[$type][$filename]) || $this->fileAccess->exists($type, $filename);
    }

    public function write(string $type, string $filename, string $content): void
    {
        $this->cachedFiles[$type][$filename] = $content;

        $this->fileAccess->write($type, $filename, $content);
    }

    public function delete(string $type, string $filename): void
    {
        unset($this->cachedFiles[$type][$filename]);

        $this->fileAccess->delete($type, $filename);
    }

    public function prune(string $type): void
    {
        unset($this->cachedFiles[$type]);
        $this->fileAccess->prune($type);
    }
}
