<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\ImageGenerator\Application\Command;

use ChronicleKeeper\ImageGenerator\Application\Command\StoreGeneratorRequest;
use ChronicleKeeper\ImageGenerator\Application\Command\StoreGeneratorRequestHandler;
use ChronicleKeeper\ImageGenerator\Application\Service\PromptOptimizer;
use ChronicleKeeper\ImageGenerator\Domain\ValueObject\OptimizedPrompt;
use ChronicleKeeper\Test\ImageGenerator\Domain\Entity\GeneratorRequestBuilder;
use ChronicleKeeper\Test\Shared\Infrastructure\Database\DatabaseTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Large;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Component\Serializer\SerializerInterface;

#[CoversClass(StoreGeneratorRequestHandler::class)]
#[CoversClass(StoreGeneratorRequest::class)]
#[Large]
class StoreGeneratorRequestHandlerTest extends DatabaseTestCase
{
    #[Test]
    public function testStoreRequestWithOptimizedPrompt(): void
    {
        $serializer      = $this->createMock(SerializerInterface::class);
        $promptOptimizer = $this->createMock(PromptOptimizer::class);

        $handler = new StoreGeneratorRequestHandler($serializer, $promptOptimizer, $this->connection);
        $request = new StoreGeneratorRequest((new GeneratorRequestBuilder())->withOptimizedPrompt(null)->build());

        $promptOptimizer->expects($this->once())
            ->method('optimize')
            ->willReturn('Optimized Prompt');

        $handler($request);

        self::assertSame('Optimized Prompt', $request->request->prompt?->prompt);

        $rawGeneratorRequests = $this->getRowFromTable('generator_requests', 'id', $request->request->id);
        self::assertNotNull($rawGeneratorRequests);
        self::assertSame($request->request->id, $rawGeneratorRequests['id']);
        self::assertSame('Optimized Prompt', $rawGeneratorRequests['prompt']);
    }

    #[Test]
    public function testStoreRequestWithoutOptimizedPrompt(): void
    {
        $serializer      = $this->createMock(SerializerInterface::class);
        $promptOptimizer = $this->createMock(PromptOptimizer::class);

        $handler = new StoreGeneratorRequestHandler($serializer, $promptOptimizer, $this->connection);
        $request = new StoreGeneratorRequest((new GeneratorRequestBuilder())
            ->withOptimizedPrompt(new OptimizedPrompt('Already Optimized Prompt'))
            ->build());

        $promptOptimizer->expects($this->never())->method('optimize');

        $handler($request);

        $rawGeneratorRequests = $this->getRowFromTable('generator_requests', 'id', $request->request->id);
        self::assertNotNull($rawGeneratorRequests);
        self::assertSame($request->request->id, $rawGeneratorRequests['id']);
        self::assertSame('Already Optimized Prompt', $rawGeneratorRequests['prompt']);
    }
}
