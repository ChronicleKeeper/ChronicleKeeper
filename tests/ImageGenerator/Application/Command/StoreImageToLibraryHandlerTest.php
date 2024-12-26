<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\ImageGenerator\Application\Command;

use ChronicleKeeper\Image\Domain\Entity\Image;
use ChronicleKeeper\ImageGenerator\Application\Command\StoreImageToLibrary;
use ChronicleKeeper\ImageGenerator\Application\Command\StoreImageToLibraryHandler;
use ChronicleKeeper\ImageGenerator\Domain\ValueObject\OptimizedPrompt;
use ChronicleKeeper\Library\Application\Service\Image\LLMDescriber;
use ChronicleKeeper\Library\Infrastructure\Repository\FilesystemImageRepository;
use ChronicleKeeper\Shared\Application\Query\QueryService;
use ChronicleKeeper\Shared\Infrastructure\Persistence\Filesystem\Contracts\FileAccess;
use ChronicleKeeper\Test\ImageGenerator\Domain\Entity\GeneratorRequestBuilder;
use ChronicleKeeper\Test\ImageGenerator\Domain\Entity\GeneratorResultBuilder;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use stdClass;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Webmozart\Assert\InvalidArgumentException;

#[CoversClass(StoreImageToLibraryHandler::class)]
#[CoversClass(StoreImageToLibrary::class)]
#[Small]
class StoreImageToLibraryHandlerTest extends TestCase
{
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
        $fileAccess = $this->createMock(FileAccess::class);
        $serializer = $this->createMock(SerializerInterface::class);

        $imageRepository = $this->createMock(FilesystemImageRepository::class);
        $imageRepository->expects($this->once())
            ->method('store')
            ->with(self::callback(static function (Image $image): bool {
                self::assertSame('Foo Bar', $image->getTitle());
                self::assertSame('image/png', $image->getMimeType());
                self::assertSame('Encoded Image', $image->getEncodedImage());
                self::assertSame('Generated Image Prompt', $image->getDescription());

                return true;
            }));

        $queryService = $this->createMock(QueryService::class);

        $invoker = $this->exactly(2);
        $queryService->expects($invoker)
            ->method('query')
            ->willReturnOnConsecutiveCalls(
                (new GeneratorRequestBuilder())
                    ->withTitle('Foo Bar')
                    ->withOptimizedPrompt(new OptimizedPrompt('Optimized Prompt'))
                    ->build(),
                (new GeneratorResultBuilder())->withEncodedImage('Encoded Image')->build(),
            );

        $LLMDescriber = $this->createMock(LLMDescriber::class);
        $LLMDescriber->expects($this->once())
            ->method('getDescription')
            ->with(self::isInstanceOf(Image::class))
            ->willReturn('Generated Image Prompt');

        $bus = $this->createMock(MessageBusInterface::class);
        $bus->expects($this->once())
            ->method('dispatch')
            ->willReturn(new Envelope(new stdClass()));

        $handler = new StoreImageToLibraryHandler(
            $fileAccess,
            $serializer,
            $imageRepository,
            $queryService,
            $LLMDescriber,
            $bus,
        );

        $request = new StoreImageToLibrary(
            '123e4567-e89b-12d3-a456-426614174000',
            '123e4567-e89b-12d3-a456-426614174001',
        );

        $handler($request);
    }
}
