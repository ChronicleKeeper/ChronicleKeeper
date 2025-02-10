<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\ImageGenerator\Application\Query;

use ChronicleKeeper\ImageGenerator\Application\Command\StoreGeneratorRequest;
use ChronicleKeeper\ImageGenerator\Application\Command\StoreGeneratorResult;
use ChronicleKeeper\ImageGenerator\Application\Query\FindAllImagesOfRequest;
use ChronicleKeeper\ImageGenerator\Application\Query\FindAllImagesOfRequestQuery;
use ChronicleKeeper\Test\ImageGenerator\Domain\Entity\GeneratorRequestBuilder;
use ChronicleKeeper\Test\ImageGenerator\Domain\Entity\GeneratorResultBuilder;
use ChronicleKeeper\Test\Shared\Infrastructure\Database\DatabaseTestCase;
use Override;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Large;
use PHPUnit\Framework\Attributes\Test;
use Webmozart\Assert\InvalidArgumentException;

use function assert;

#[CoversClass(FindAllImagesOfRequestQuery::class)]
#[CoversClass(FindAllImagesOfRequest::class)]
#[Large]
class FindAllImagesOfRequestQueryTest extends DatabaseTestCase
{
    private FindAllImagesOfRequestQuery $query;

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        $query = self::getContainer()->get(FindAllImagesOfRequestQuery::class);
        assert($query instanceof FindAllImagesOfRequestQuery);

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
            FindAllImagesOfRequestQuery::class,
            (new FindAllImagesOfRequest('b06bd1f2-f7bb-43ca-948e-7fe38956667e'))->getQueryClass(),
        );
    }

    #[Test]
    public function aRequestIdentifierHaveToBeAnUuid(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Value "foo-bar-baz" is not a valid UUID.');

        new FindAllImagesOfRequest('foo-bar-baz');
    }

    #[Test]
    public function theImagesArrayIsBuild(): void
    {
        // ------------------- The test setup -------------------

        $request = (new GeneratorRequestBuilder())->build();
        $this->bus->dispatch(new StoreGeneratorRequest($request));

        $result1 = (new GeneratorResultBuilder())->build();
        $result2 = (new GeneratorResultBuilder())->build();

        $this->bus->dispatch(new StoreGeneratorResult($request->id, $result1));
        $this->bus->dispatch(new StoreGeneratorResult($request->id, $result2));

        // ------------------- Execute tests --------------------

        $response = $this->query->query(new FindAllImagesOfRequest($request->id));

        // ------------------- The test assertions -------------------

        self::assertCount(2, $response);
        self::assertSame($result1->id, $response[0]->id);
        self::assertSame($result2->id, $response[1]->id);
    }
}
