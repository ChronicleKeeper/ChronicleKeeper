<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\ImageGenerator\Application\Query;

use ChronicleKeeper\Image\Application\Command\StoreImage;
use ChronicleKeeper\Image\Domain\Entity\Image;
use ChronicleKeeper\ImageGenerator\Application\Command\StoreGeneratorRequest;
use ChronicleKeeper\ImageGenerator\Application\Command\StoreGeneratorResult;
use ChronicleKeeper\ImageGenerator\Application\Query\GetImageOfGeneratorRequest;
use ChronicleKeeper\ImageGenerator\Application\Query\GetImageOfGeneratorRequestQuery;
use ChronicleKeeper\Test\Image\Domain\Entity\ImageBuilder;
use ChronicleKeeper\Test\ImageGenerator\Domain\Entity\GeneratorRequestBuilder;
use ChronicleKeeper\Test\ImageGenerator\Domain\Entity\GeneratorResultBuilder;
use ChronicleKeeper\Test\Shared\Infrastructure\Database\SQLite\DatabaseTestCase;
use Override;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Large;
use PHPUnit\Framework\Attributes\Test;
use Webmozart\Assert\InvalidArgumentException;

use function assert;

#[CoversClass(GetImageOfGeneratorRequestQuery::class)]
#[CoversClass(GetImageOfGeneratorRequest::class)]
#[Large]
class GetImageOfGeneratorRequestQueryTest extends DatabaseTestCase
{
    private GetImageOfGeneratorRequestQuery $query;

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        $query = self::getContainer()->get(GetImageOfGeneratorRequestQuery::class);
        assert($query instanceof GetImageOfGeneratorRequestQuery);

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
        self::assertSame(GetImageOfGeneratorRequestQuery::class, (new GetImageOfGeneratorRequest(
            '5b3cde06-bc8b-4389-8407-2493a58d95e7',
            'b06bd1f2-f7bb-43ca-948e-7fe38956667e',
        ))->getQueryClass());
    }

    #[Test]
    public function aRequestIdentifierHaveToBeAUuid(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Value "foo-bar-baz" is not a valid UUID.');

        new GetImageOfGeneratorRequest(
            'foo-bar-baz',
            '51b7e687-309a-4ee4-9d11-d6fbd977acde',
        );
    }

    #[Test]
    public function anImageIdentifierHaveToBeAUuid(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Value "foo-bar-baz" is not a valid UUID.');

        new GetImageOfGeneratorRequest(
            '51b7e687-309a-4ee4-9d11-d6fbd977acde',
            'foo-bar-baz',
        );
    }

    #[Test]
    public function theGeneratorResultIsBuild(): void
    {
        // ------------------- The test setup -------------------

        $request = (new GeneratorRequestBuilder())->build();
        $this->bus->dispatch(new StoreGeneratorRequest($request));

        $image = (new ImageBuilder())->build();
        $this->bus->dispatch(new StoreImage($image));

        $result = (new GeneratorResultBuilder())->withImage($image)->build();
        $this->bus->dispatch(new StoreGeneratorResult($request->id, $result));

        assert($result->image instanceof Image);

        // ------------------- The test execution -------------------

        $searchResult = $this->query->query(new GetImageOfGeneratorRequest(
            $request->id,
            $result->id,
        ));

        // ------------------- The test assertions -------------------

        self::assertSame($result->id, $searchResult->id);
        self::assertSame(
            $result->image->getId(),
            $searchResult->image?->getId(),
        );
    }
}
