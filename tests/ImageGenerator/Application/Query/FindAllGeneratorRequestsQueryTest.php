<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\ImageGenerator\Application\Query;

use ChronicleKeeper\ImageGenerator\Application\Command\StoreGeneratorRequest;
use ChronicleKeeper\ImageGenerator\Application\Query\FindAllGeneratorRequests;
use ChronicleKeeper\ImageGenerator\Application\Query\FindAllGeneratorRequestsQuery;
use ChronicleKeeper\Test\ImageGenerator\Domain\Entity\GeneratorRequestBuilder;
use ChronicleKeeper\Test\Shared\Infrastructure\Database\DatabaseTestCase;
use Override;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Large;
use PHPUnit\Framework\Attributes\Test;

use function assert;

#[CoversClass(FindAllGeneratorRequestsQuery::class)]
#[CoversClass(FindAllGeneratorRequests::class)]
#[Large]
class FindAllGeneratorRequestsQueryTest extends DatabaseTestCase
{
    private FindAllGeneratorRequestsQuery $query;

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        $query = self::getContainer()->get(FindAllGeneratorRequestsQuery::class);
        assert($query instanceof FindAllGeneratorRequestsQuery);

        $this->query = $query;
    }

    #[Override]
    protected function tearDown(): void
    {
        parent::tearDown();

        unset($this->query);
    }

    #[Test]
    public function correctQueryClassIsliked(): void
    {
        self::assertSame(
            FindAllGeneratorRequestsQuery::class,
            (new FindAllGeneratorRequests())->getQueryClass(),
        );
    }

    #[Test]
    public function isDeliveringFoundGeneratorRequests(): void
    {
        // ------------------- The test setup -------------------

        $generatorRequest = (new GeneratorRequestBuilder())->withTitle('foo')->build();
        $this->bus->dispatch(new StoreGeneratorRequest($generatorRequest));
        $generatorRequest = (new GeneratorRequestBuilder())->withTitle('bar')->build();
        $this->bus->dispatch(new StoreGeneratorRequest($generatorRequest));

        // ------------------- The actual test -------------------

        $results = $this->query->query(new FindAllGeneratorRequests());

        // ------------------- Test Assertions -------------------

        self::assertCount(2, $results);
        self::assertSame('bar', $results[0]->title);
        self::assertSame('foo', $results[1]->title);
    }
}
