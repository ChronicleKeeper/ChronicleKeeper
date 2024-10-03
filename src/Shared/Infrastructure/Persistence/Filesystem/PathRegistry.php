<?php

declare(strict_types=1);

namespace ChronicleKeeper\Shared\Infrastructure\Persistence\Filesystem;

use ChronicleKeeper\Shared\Infrastructure\Persistence\Filesystem\Exception\PathNotRegistered;
use Webmozart\Assert\Assert;

use function array_key_exists;

class PathRegistry
{
    /** @var array<non-empty-string, non-empty-string> */
    private array $paths = [];

    public function has(string $type): bool
    {
        return array_key_exists($type, $this->paths);
    }

    public function add(string $type, string $path): void
    {
        Assert::notEmpty($type, 'A path must not have an empty type, an example would be "documents".');
        Assert::notEmpty($path, 'The path must not be empty, an example would be "/var/www/data/documents"');

        $this->paths[$type] = $path;
    }

    /**
     * @return non-empty-string
     *
     * @throws PathNotRegistered if there is no path existing for the requested type.
     */
    public function get(string $type): string
    {
        return $this->paths[$type] ?? throw new PathNotRegistered($type);
    }
}
