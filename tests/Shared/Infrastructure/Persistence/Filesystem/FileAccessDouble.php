<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Shared\Infrastructure\Persistence\Filesystem;

use ChronicleKeeper\Shared\Infrastructure\Persistence\Filesystem\Contracts\FileAccess;
use ChronicleKeeper\Shared\Infrastructure\Persistence\Filesystem\Exception\UnableToReadFile;

use function array_key_exists;

class FileAccessDouble implements FileAccess
{
    /** @var array<string, string> */
    private array $storage = [];

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

    private function getPath(string $type, string $filename): string
    {
        return $type . '/' . $filename;
    }
}
