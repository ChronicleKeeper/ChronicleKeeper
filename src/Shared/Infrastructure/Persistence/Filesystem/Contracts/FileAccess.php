<?php

declare(strict_types=1);

namespace ChronicleKeeper\Shared\Infrastructure\Persistence\Filesystem\Contracts;

use ChronicleKeeper\Shared\Infrastructure\Persistence\Filesystem\Exception\UnableToDeleteFile;
use ChronicleKeeper\Shared\Infrastructure\Persistence\Filesystem\Exception\UnableToReadFile;
use ChronicleKeeper\Shared\Infrastructure\Persistence\Filesystem\Exception\UnableToWriteFile;

interface FileAccess
{
    /**
     * Read a file from the filesystem.
     *
     * @param non-empty-string $type
     *
     * @throws UnableToReadFile
     */
    public function read(string $type, string $filename): string;

    /**
     * Checks if the file exists in the filesystem.
     *
     * @param non-empty-string $type
     */
    public function exists(string $type, string $filename): bool;

    /**
     * Write a file to the filesystem.
     *
     * @param non-empty-string $type
     *
     * @throws UnableToWriteFile
     */
    public function write(string $type, string $filename, string $content): void;

    /**
     * Delete a file from the filesystem.
     *
     * @param non-empty-string $type
     *
     * @throws UnableToDeleteFile
     */
    public function delete(string $type, string $filename): void;

    /** @param non-empty-string $type */
    public function prune(string $type): void;
}
