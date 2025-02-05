<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\ImageGenerator\Application\Query;

use ChronicleKeeper\ImageGenerator\Application\Command\StoreGeneratorRequest;
use ChronicleKeeper\ImageGenerator\Application\Query\GetGeneratorRequest;
use ChronicleKeeper\ImageGenerator\Application\Query\GetGeneratorRequestQuery;
use ChronicleKeeper\Test\ImageGenerator\Domain\Entity\GeneratorRequestBuilder;
use ChronicleKeeper\Test\Shared\Infrastructure\Database\SQLite\DatabaseTestCase;
use Override;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Large;
use PHPUnit\Framework\Attributes\Test;
use Webmozart\Assert\InvalidArgumentException;

use function assert;

#[CoversClass(GetGeneratorRequestQuery::class)]
#[CoversClass(GetGeneratorRequest::class)]
#[Large]
class GetGeneratorRequestQueryTest extends DatabaseTestCase
{
    private GetGeneratorRequestQuery $query;

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        $query = self::getContainer()->get(GetGeneratorRequestQuery::class);
        assert($query instanceof GetGeneratorRequestQuery);

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
            GetGeneratorRequestQuery::class,
            (new GetGeneratorRequest('b06bd1f2-f7bb-43ca-948e-7fe38956667e'))->getQueryClass(),
        );
    }

    #[Test]
    public function anIdentifierHaveToBeAnUuid(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Value "foo-bar-baz" is not a valid UUID.');

        new GetGeneratorRequest('foo-bar-baz');
    }

    #[Test]
    public function theGeneratorRequestIsBuild(): void
    {
        // ------------------- The test setup -------------------

        $generatorRequest = (new GeneratorRequestBuilder())->build();
        $this->bus->dispatch(new StoreGeneratorRequest($generatorRequest));

        // ------------------- Execute tests --------------------

        $result = $this->query->query(new GetGeneratorRequest($generatorRequest->id));

        // ------------------- The test assertions -------------------

        self::assertSame($generatorRequest->id, $result->id);
        self::assertSame($generatorRequest->prompt?->prompt, $result->prompt?->prompt);
    }
}
