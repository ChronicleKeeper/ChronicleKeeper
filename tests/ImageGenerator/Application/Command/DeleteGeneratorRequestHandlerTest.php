<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\ImageGenerator\Application\Command;

use ChronicleKeeper\ImageGenerator\Application\Command\DeleteGeneratorRequest;
use ChronicleKeeper\ImageGenerator\Application\Command\DeleteGeneratorRequestHandler;
use ChronicleKeeper\ImageGenerator\Application\Service\PromptOptimizer;
use ChronicleKeeper\Shared\Infrastructure\Persistence\Filesystem\Contracts\FileAccess;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\SerializerInterface;
use Webmozart\Assert\InvalidArgumentException;

#[CoversClass(DeleteGeneratorRequestHandler::class)]
#[CoversClass(DeleteGeneratorRequest::class)]
#[Small]
class DeleteGeneratorRequestHandlerTest extends TestCase
{
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
        $fileAccess      = $this->createMock(FileAccess::class);
        $serializer      = $this->createMock(SerializerInterface::class);
        $promptOptimizer = $this->createMock(PromptOptimizer::class);

        $handler = new DeleteGeneratorRequestHandler($fileAccess, $serializer, $promptOptimizer);
        $request = new DeleteGeneratorRequest('30da9c8d-7c80-4404-b8d2-5fa196e9548c');

        $invoker = $this->exactly(2);
        $fileAccess->expects($invoker)
            ->method('delete')
            ->willReturnCallback(static function (string $path, string $filename) use ($invoker): void {
                if ($invoker->numberOfInvocations() === 1) {
                    self::assertSame('generator.images', $path);
                    self::assertSame('30da9c8d-7c80-4404-b8d2-5fa196e9548c', $filename);

                    return;
                }

                self::assertSame('generator.requests', $path);
                self::assertSame('30da9c8d-7c80-4404-b8d2-5fa196e9548c.json', $filename);
            });

        $handler($request);
    }
}
