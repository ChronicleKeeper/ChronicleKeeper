<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Shared\Infrastructure\Persistence\Filesystem;

use ChronicleKeeper\Shared\Infrastructure\Persistence\Filesystem\Contracts\FileAccess;
use ChronicleKeeper\Shared\Infrastructure\Persistence\Filesystem\Exception\UnableToReadFile;
use Symfony\Contracts\Service\ResetInterface;

use function array_key_exists;
use function str_replace;
use function str_starts_with;

class FileAccessDouble implements FileAccess, ResetInterface
{
    /** @var array<string, string> */
    private array $storage = [];

    /** @return array<string, string> */
    public function allOfType(string $type): array
    {
        $files = [];
        foreach ($this->storage as $path => $content) {
            if (! str_starts_with($path, $type)) {
                continue;
            }

            $files[str_replace($type . '/', '', $path)] = $content;
        }

        return $files;
    }

    public function read(string $type, string $filename): string
    {
        $path = $this->getPath($type, $filename);
        if (! $this->exists($type, $filename)) {
            throw new UnableToReadFile($path);
        }

        return $this->storage[$path];
    }

    public function exists(string $type, string $filename): bool
    {
        $path = $this->getPath($type, $filename);

        return array_key_exists($path, $this->storage);
    }

    public function write(string $type, string $filename, string $content): void
    {
        $path                 = $this->getPath($type, $filename);
        $this->storage[$path] = $content;
    }

    public function delete(string $type, string $filename): void
    {
        $path = $this->getPath($type, $filename);

        if (! $this->exists($type, $filename)) {
            // All fine, file not exists ...
            return;
        }

        unset($this->storage[$path]);
    }

    public function reset(): void
    {
        $this->storage = [];
    }

    public function prune(string $type): void
    {
        unset($this->storage[$type]);
    }

    private function getPath(string $type, string $filename): string
    {
        return $type . '/' . $filename;
    }
}
