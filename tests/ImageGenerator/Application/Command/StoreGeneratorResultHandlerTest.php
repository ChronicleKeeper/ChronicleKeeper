<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\ImageGenerator\Application\Command;

use ChronicleKeeper\ImageGenerator\Application\Command\StoreGeneratorRequest;
use ChronicleKeeper\ImageGenerator\Application\Command\StoreGeneratorResult;
use ChronicleKeeper\ImageGenerator\Application\Command\StoreGeneratorResultHandler;
use ChronicleKeeper\Test\ImageGenerator\Domain\Entity\GeneratorRequestBuilder;
use ChronicleKeeper\Test\ImageGenerator\Domain\Entity\GeneratorResultBuilder;
use ChronicleKeeper\Test\Shared\Infrastructure\Database\DatabaseTestCase;
use Override;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Large;
use PHPUnit\Framework\Attributes\Test;
use Webmozart\Assert\InvalidArgumentException;

use function assert;

#[CoversClass(StoreGeneratorResultHandler::class)]
#[CoversClass(StoreGeneratorResult::class)]
#[Large]
class StoreGeneratorResultHandlerTest extends DatabaseTestCase
{
    private StoreGeneratorResultHandler $handler;

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        $handler = self::getContainer()->get(StoreGeneratorResultHandler::class);
        assert($handler instanceof StoreGeneratorResultHandler);

        $this->handler = $handler;
    }

    #[Override]
    protected function tearDown(): void
    {
        parent::tearDown();

        unset($this->handler);
    }

    #[Test]
    public function testInvalidRequestId(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Value "invalid-request-id" is not a valid UUID.');

        $generatorResult = (new GeneratorResultBuilder())->build();
        new StoreGeneratorResult('invalid-request-id', $generatorResult);
    }

    #[Test]
    public function testStoreGeneratorResult(): void
    {
        // ------------------- The test setup -------------------

        $request = (new GeneratorRequestBuilder())->build();
        $this->bus->dispatch(new StoreGeneratorRequest($request));

        $result = (new GeneratorResultBuilder())->build();

        // ------------------- The test setup -------------------

        ($this->handler)(new StoreGeneratorResult($request->id, $result));

        // ------------------- The test assertions -------------------

        $this->assertRowsInTable('generator_results', 1);
    }
}
