<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\ImageGenerator\Application\Command;

use ChronicleKeeper\ImageGenerator\Application\Command\StoreGeneratorRequest;
use ChronicleKeeper\ImageGenerator\Application\Command\StoreGeneratorRequestHandler;
use ChronicleKeeper\ImageGenerator\Application\Service\PromptOptimizer;
use ChronicleKeeper\ImageGenerator\Domain\ValueObject\OptimizedPrompt;
use ChronicleKeeper\Shared\Infrastructure\Database\DatabasePlatform;
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
        $serializer       = $this->createMock(SerializerInterface::class);
        $promptOptimizer  = $this->createMock(PromptOptimizer::class);
        $databasePlatform = $this->createMock(DatabasePlatform::class);

        $handler = new StoreGeneratorRequestHandler($serializer, $promptOptimizer, $databasePlatform);
        $request = new StoreGeneratorRequest((new GeneratorRequestBuilder())->build());

        $promptOptimizer->expects($this->once())
            ->method('optimize')
            ->willReturn('Optimized Prompt');

        $databasePlatform->expects($this->once())
            ->method('insertOrUpdate')
            ->with(
                'generator_requests',
                [
                    'id'       => $request->request->id,
                    'title'    => $request->request->title,
                    'userInput' => '',
                    'prompt'   => 'Optimized Prompt',
                ],
            );

        $handler($request);

        self::assertSame('Optimized Prompt', $request->request->prompt?->prompt);
    }

    #[Test]
    public function testStoreRequestWithoutOptimizedPrompt(): void
    {
        $serializer       = $this->createMock(SerializerInterface::class);
        $promptOptimizer  = $this->createMock(PromptOptimizer::class);
        $databasePlatform = $this->createMock(DatabasePlatform::class);

        $handler = new StoreGeneratorRequestHandler($serializer, $promptOptimizer, $databasePlatform);
        $request = new StoreGeneratorRequest((new GeneratorRequestBuilder())
            ->withOptimizedPrompt(new OptimizedPrompt('Already Optimized Prompt'))
            ->build());

        $promptOptimizer->expects($this->never())->method('optimize');

        $databasePlatform->expects($this->once())
            ->method('insertOrUpdate')
            ->with(
                'generator_requests',
                [
                    'id'       => $request->request->id,
                    'title'    => $request->request->title,
                    'userInput' => '',
                    'prompt'   => 'Already Optimized Prompt',
                ],
            );

        $handler($request);
    }
}
