<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\ImageGenerator\Application\Command;

use ChronicleKeeper\ImageGenerator\Application\Command\StoreGeneratorRequest;
use ChronicleKeeper\ImageGenerator\Application\Command\StoreGeneratorResult;
use ChronicleKeeper\ImageGenerator\Application\Command\StoreImageToLibrary;
use ChronicleKeeper\ImageGenerator\Application\Command\StoreImageToLibraryHandler;
use ChronicleKeeper\Shared\Infrastructure\LLMChain\LLMChainFactory;
use ChronicleKeeper\Test\ImageGenerator\Domain\Entity\GeneratorRequestBuilder;
use ChronicleKeeper\Test\ImageGenerator\Domain\Entity\GeneratorResultBuilder;
use ChronicleKeeper\Test\Shared\Infrastructure\Database\SQLite\DatabaseTestCase;
use ChronicleKeeper\Test\Shared\Infrastructure\LLMChain\LLMChainFactoryDouble;
use Override;
use PhpLlm\LlmChain\Bridge\OpenAI\Embeddings;
use PhpLlm\LlmChain\Document\Vector;
use PhpLlm\LlmChain\Model\Response\ResponseInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Large;
use PHPUnit\Framework\Attributes\Test;
use Webmozart\Assert\InvalidArgumentException;

use function array_map;
use function assert;
use function mt_getrandmax;
use function mt_rand;
use function range;

#[CoversClass(StoreImageToLibraryHandler::class)]
#[CoversClass(StoreImageToLibrary::class)]
#[Large]
class StoreImageToLibraryHandlerTest extends DatabaseTestCase
{
    private StoreImageToLibraryHandler $handler;

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        $handler = self::getContainer()->get(StoreImageToLibraryHandler::class);
        assert($handler instanceof StoreImageToLibraryHandler);

        $this->handler = $handler;
    }

    #[Override]
    protected function tearDown(): void
    {
        parent::tearDown();

        unset($this->handler);
    }

    #[Test]
    public function invalidRequestIdentifier(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Value "invalid-string" is not a valid UUID.');

        new StoreImageToLibrary(
            'invalid-string',
            '123e4567-e89b-12d3-a456-426614174001',
        );
    }

    #[Test]
    public function invalidImageIdentifier(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Value "invalid-identifier" is not a valid UUID.');

        new StoreImageToLibrary(
            '123e4567-e89b-12d3-a456-426614174000',
            'invalid-identifier',
        );
    }

    #[Test]
    public function testStoreImageToLibrary(): void
    {
        // ------------------- The test setup -------------------

        $request = (new GeneratorRequestBuilder())->build();
        $this->bus->dispatch(new StoreGeneratorRequest($request));

        $result = (new GeneratorResultBuilder())->build();
        $this->bus->dispatch(new StoreGeneratorResult($request->id, $result));

        $llmChainFactory = $this->client->getContainer()->get(LLMChainFactory::class);
        assert($llmChainFactory instanceof LLMChainFactoryDouble);

        $llmChainFactory->addPlatformResponse(
            Embeddings::class,
            new class implements ResponseInterface {
                /** @return Vector[] */
                public function getContent(): array
                {
                    return [new Vector(array_map(static fn () => mt_rand() / mt_getrandmax(), range(1, 1536)))];
                }
            },
        );

        // ------------------- The test scenario -------------------

        ($this->handler)(new StoreImageToLibrary($request->id, $result->id));

        // ------------------- The test assertions -------------------

        $this->assertRowsInTable('images', 1);
    }
}
