<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Image\Application\Query;

use ChronicleKeeper\Image\Application\Command\StoreImage;
use ChronicleKeeper\Image\Application\Query\FindImagesByDirectory;
use ChronicleKeeper\Image\Application\Query\FindImagesByDirectoryQuery;
use ChronicleKeeper\Library\Application\Command\StoreDirectory;
use ChronicleKeeper\Test\Image\Domain\Entity\ImageBuilder;
use ChronicleKeeper\Test\Library\Domain\Entity\DirectoryBuilder;
use ChronicleKeeper\Test\Shared\Infrastructure\Database\SQLite\DatabaseTestCase;
use InvalidArgumentException;
use Override;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Large;
use PHPUnit\Framework\Attributes\Test;

use function assert;

#[CoversClass(FindImagesByDirectoryQuery::class)]
#[CoversClass(FindImagesByDirectory::class)]
#[Large]
final class FindImagesByDirectoryTest extends DatabaseTestCase
{
    private FindImagesByDirectoryQuery $query;

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        $query = self::getContainer()->get(FindImagesByDirectoryQuery::class);
        assert($query instanceof FindImagesByDirectoryQuery);

        $this->query = $query;
    }

    #[Override]
    protected function tearDown(): void
    {
        parent::tearDown();

        unset($this->query);
    }

    #[Test]
    public function itHasACorrectQueryClassInUse(): void
    {
        $directory = (new DirectoryBuilder())->build();

        self::assertSame(
            FindImagesByDirectoryQuery::class,
            (new FindImagesByDirectory($directory->getId()))->getQueryClass(),
        );
    }

    #[Test]
    public function itHasANonCreatableQueryClassWithInvalidIdentifier(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Value "invalid-uuid" is not a valid UUID.');

        new FindImagesByDirectory('invalid-uuid');
    }

    #[Test]
    public function itIsAbleToFindImagesByADirectory(): void
    {
        // ------------------- The test setup -------------------

        $searchDirectory = (new DirectoryBuilder())->withTitle('My Directory')->build();
        $this->bus->dispatch(new StoreDirectory($searchDirectory));

        $image = (new ImageBuilder())
            ->withId('b48d23bc-7a04-4483-81da-56c72dbdc628')
            ->withTitle('foo')
            ->withDirectory($searchDirectory)
            ->build();
        $this->bus->dispatch(new StoreImage($image));

        $image = (new ImageBuilder())
            ->withId('71fabba4-7a4e-4758-b07e-0ebaa2063748')
            ->withTitle('bar')
            ->build();
        $this->bus->dispatch(new StoreImage($image));

        // ------------------- The test scenario -------------------

        $images = $this->query->query(new FindImagesByDirectory($searchDirectory->getId()));

        // ------------------- The test assertions -------------------

        self::assertCount(1, $images);
        self::assertSame('b48d23bc-7a04-4483-81da-56c72dbdc628', $images[0]->getId());
    }
}
