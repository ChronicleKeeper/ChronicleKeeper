<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Shared\Infrastructure\Persistence\Filesystem;

use ChronicleKeeper\Shared\Infrastructure\Persistence\Filesystem\Exception\PathNotRegistered;
use ChronicleKeeper\Shared\Infrastructure\Persistence\Filesystem\PathRegistry;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(PathRegistry::class)]
#[Small]
class PathRegistryTest extends TestCase
{
    private PathRegistry $pathRegistry;

    protected function setUp(): void
    {
        $this->pathRegistry = new PathRegistry();
    }

    #[Test]
    public function addAndGetPath(): void
    {
        $type = 'images';
        $path = '/var/www/data/images';

        $this->pathRegistry->add($type, $path);
        $retrievedPath = $this->pathRegistry->get($type);

        self::assertSame($path, $retrievedPath);
    }

    #[Test]
    public function hasPath(): void
    {
        $type = 'documents';
        $path = '/var/www/data/documents';

        $this->pathRegistry->add($type, $path);

        self::assertTrue($this->pathRegistry->has($type));
        self::assertFalse($this->pathRegistry->has('nonexistent'));
    }

    #[Test]
    public function getPathThrowsExceptionForUnregisteredType(): void
    {
        $this->expectException(PathNotRegistered::class);
        $this->pathRegistry->get('nonexistent');
    }

    #[Test]
    public function addPathWithEmptyTypeThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->pathRegistry->add('', '/var/www/data/emptytype');
    }

    #[Test]
    public function addPathWithEmptyPathThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->pathRegistry->add('emptyPath', '');
    }
}
