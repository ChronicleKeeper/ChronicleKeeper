<?php

declare(strict_types=1);

namespace ChronicleKeeper\Shared\Infrastructure\Persistence\Filesystem;

use ChronicleKeeper\Shared\Infrastructure\Persistence\Filesystem\Contracts\FileAccess as FileAccessContract;
use ChronicleKeeper\Shared\Infrastructure\Persistence\Filesystem\Exception\UnableToDeleteFile;
use ChronicleKeeper\Shared\Infrastructure\Persistence\Filesystem\Exception\UnableToReadFile;
use ChronicleKeeper\Shared\Infrastructure\Persistence\Filesystem\Exception\UnableToWriteFile;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Filesystem\Filesystem;

use const DIRECTORY_SEPARATOR;

class FileAccess implements FileAccessContract
{
    private array $cache = [];

    public function __construct(
        private readonly PathRegistry $pathRegistry,
        private readonly Filesystem $filesystem,
    ) {
    }

    public function read(string $type, string $filename): string
    {
        $path = $this->buildPath($type, $filename);

        if (isset($this->cache[$path])) {
            return $this->cache[$path];
        }

        if (! $this->filesystem->exists($path)) {
            throw new UnableToReadFile($path);
        }

        try {
            $content = $this->filesystem->readFile($path);
            $this->cache[$path] = $content;
            return $content;
        } catch (IOException) {
            throw new UnableToReadFile($path);
        }
    }

    public function exists(string $type, string $filename): bool
    {
        $path = $this->buildPath($type, $filename);

        return $this->filesystem->exists($path);
    }

    public function write(string $type, string $filename, string $content): void
    {
        $path = $this->buildPath($type, $filename);

        try {
            $this->filesystem->dumpFile($path, $content);
            $this->cache[$path] = $content;
        } catch (IOException) {
            throw new UnableToWriteFile($path);
        }
    }

    public function delete(string $type, string $filename): void
    {
        $path = $this->buildPath($type, $filename);

        if (! $this->filesystem->exists($path)) {
            // All Fine, when it not exists :)
            return;
        }

        try {
            $this->filesystem->remove($path);
            unset($this->cache[$path]);
        } catch (IOException) {
            throw new UnableToDeleteFile($path);
        }
    }

    public function readIndex(string $type): array
    {
        $indexPath = $this->buildPath($type, 'index.json');

        if (! $this->filesystem->exists($indexPath)) {
            return [];
        }

        try {
            $content = $this->filesystem->readFile($indexPath);
            return json_decode($content, true);
        } catch (IOException) {
            throw new UnableToReadFile($indexPath);
        }
    }

    public function writeIndex(string $type, array $index): void
    {
        $indexPath = $this->buildPath($type, 'index.json');

        try {
            $content = json_encode($index);
            $this->filesystem->dumpFile($indexPath, $content);
        } catch (IOException) {
            throw new UnableToWriteFile($indexPath);
        }
    }

    private function buildPath(string $type, string $filename): string
    {
        return $this->pathRegistry->get($type) . DIRECTORY_SEPARATOR . $filename;
    }
}
