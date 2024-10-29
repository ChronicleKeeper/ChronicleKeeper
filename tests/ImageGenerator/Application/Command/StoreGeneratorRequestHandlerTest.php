<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\ImageGenerator\Application\Command;

use ChronicleKeeper\ImageGenerator\Application\Command\StoreGeneratorRequest;
use ChronicleKeeper\ImageGenerator\Application\Command\StoreGeneratorRequestHandler;
use ChronicleKeeper\ImageGenerator\Application\Service\PromptOptimizer;
use ChronicleKeeper\Shared\Infrastructure\Persistence\Filesystem\Contracts\FileAccess;
use ChronicleKeeper\Test\ImageGenerator\Domain\Entity\GeneratorRequestBuilder;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\SerializerInterface;

#[CoversClass(StoreGeneratorRequestHandler::class)]
#[CoversClass(StoreGeneratorRequest::class)]
#[Small]
class StoreGeneratorRequestHandlerTest extends TestCase
{
    #[Test]
    public function testStoreRequestWithOptimizedPrompt(): void
    {
        $fileAccess      = $this->createMock(FileAccess::class);
        $serializer      = $this->createMock(SerializerInterface::class);
        $promptOptimizer = $this->createMock(PromptOptimizer::class);

        $handler = new StoreGeneratorRequestHandler($fileAccess, $serializer, $promptOptimizer);
        $request = new StoreGeneratorRequest((new GeneratorRequestBuilder())->build());

        $promptOptimizer->expects($this->once())
            ->method('optimize')
            ->willReturn('Optimized Prompt');

        $fileAccess->expects($this->once())
            ->method('write')
            ->with(
                'generator.requests',
                $request->request->id . '.json',
                self::anything(),
            );

        $handler($request);

        self::assertSame('Optimized Prompt', $request->request->prompt?->prompt);
    }

    #[Test]
    public function testStoreRequestWithoutOptimizedPrompt(): void
    {
        $fileAccess      = $this->createMock(FileAccess::class);
        $serializer      = $this->createMock(SerializerInterface::class);
        $promptOptimizer = $this->createMock(PromptOptimizer::class);

        $handler = new StoreGeneratorRequestHandler($fileAccess, $serializer, $promptOptimizer);
        $request = new StoreGeneratorRequest((new GeneratorRequestBuilder())->build());

        $fileAccess->expects($this->once())
            ->method('write')
            ->with(
                'generator.requests',
                $request->request->id . '.json',
                self::anything(),
            );

        $handler($request);
    }
}
