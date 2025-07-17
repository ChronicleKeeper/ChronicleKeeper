<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\ImageGenerator\Application\Command;

use ChronicleKeeper\ImageGenerator\Application\Command\DeleteGeneratorRequest;
use ChronicleKeeper\ImageGenerator\Application\Command\DeleteGeneratorRequestHandler;
use ChronicleKeeper\ImageGenerator\Application\Command\StoreGeneratorRequest;
use ChronicleKeeper\Test\ImageGenerator\Domain\Entity\GeneratorRequestBuilder;
use ChronicleKeeper\Test\Shared\Infrastructure\Database\DatabaseTestCase;
use Override;
use PhpLlm\LlmChain\Platform\Bridge\OpenAI\GPT;
use PhpLlm\LlmChain\Platform\Response\TextResponse;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Large;
use PHPUnit\Framework\Attributes\Test;
use Webmozart\Assert\InvalidArgumentException;

use function assert;

#[CoversClass(DeleteGeneratorRequestHandler::class)]
#[CoversClass(DeleteGeneratorRequest::class)]
#[Large]
class DeleteGeneratorRequestHandlerTest extends DatabaseTestCase
{
    private DeleteGeneratorRequestHandler $handler;

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        $handler = self::getContainer()->get(DeleteGeneratorRequestHandler::class);
        assert($handler instanceof DeleteGeneratorRequestHandler);

        $this->handler = $handler;
    }

    #[Override]
    protected function tearDown(): void
    {
        parent::tearDown();

        unset($this->handler);
    }

    #[Test]
    public function instantiationOfCommandFailsWithNonUuid(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Value "invalid-uuid" is not a valid UUID.');

        new DeleteGeneratorRequest('invalid-uuid');
    }

    #[Test]
    public function deletionWorksAsExcpected(): void
    {
        // ------------------- The test setup -------------------

        $this->llmChainFactory->addPlatformResponse(
            GPT::class,
            new TextResponse('Hello, world!'),
        );

        $request = (new GeneratorRequestBuilder())->build();
        $this->bus->dispatch(new StoreGeneratorRequest($request));

        // ------------------- The test scenario -------------------

        ($this->handler)(new DeleteGeneratorRequest($request->id));

        // ------------------- The test assertions -------------------

        $this->assertTableIsEmpty('generator_requests');
    }
}
