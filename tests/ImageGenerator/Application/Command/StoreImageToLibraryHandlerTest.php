<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\ImageGenerator\Application\Command;

use ChronicleKeeper\Image\Domain\Entity\Image;
use ChronicleKeeper\ImageGenerator\Application\Command\StoreImageToLibrary;
use ChronicleKeeper\ImageGenerator\Application\Command\StoreImageToLibraryHandler;
use ChronicleKeeper\ImageGenerator\Domain\ValueObject\OptimizedPrompt;
use ChronicleKeeper\Library\Infrastructure\Repository\FilesystemImageRepository;
use ChronicleKeeper\Shared\Application\Query\QueryService;
use ChronicleKeeper\Shared\Infrastructure\Database\DatabasePlatform;
use ChronicleKeeper\Test\ImageGenerator\Domain\Entity\GeneratorRequestBuilder;
use ChronicleKeeper\Test\ImageGenerator\Domain\Entity\GeneratorResultBuilder;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
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
        $databasePlatform = $this->createMock(DatabasePlatform::class);

        $imageRepository = $this->createMock(FilesystemImageRepository::class);
        $imageRepository->expects($this->once())
            ->method('store')
            ->with(self::callback(static function (Image $image): bool {
                self::assertSame('Foo Bar', $image->getTitle());
                self::assertSame('image/png', $image->getMimeType());
                self::assertSame('Encoded Image', $image->getEncodedImage());
                self::assertSame('Default Prompt', $image->getDescription());

                return true;
            }));

        $queryService = $this->createMock(QueryService::class);

        $invoker = $this->exactly(2);
        $queryService->expects($invoker)
            ->method('query')
            ->willReturnOnConsecutiveCalls(
                (new GeneratorRequestBuilder())
                    ->withId('123e4567-e89b-12d3-a456-426614174000')
                    ->withTitle('Foo Bar')
                    ->withOptimizedPrompt(new OptimizedPrompt('Optimized Prompt'))
                    ->build(),
                (new GeneratorResultBuilder())
                    ->withId('123e4567-e89b-12d3-a456-426614174001')
                    ->withEncodedImage('Encoded Image')
                    ->build(),
            );

        $databasePlatform->expects($this->once())
            ->method('query')
            ->with(
                'UPDATE generator_results SET image = :image WHERE id = :id',
                self::callback(static function (array $parameters): bool {
                    self::assertNotEmpty($parameters['image']);
                    self::assertSame('123e4567-e89b-12d3-a456-426614174001', $parameters['id']);

                    return true;
                }),
            );

        $handler = new StoreImageToLibraryHandler($imageRepository, $queryService, $databasePlatform);

        $request = new StoreImageToLibrary(
            '123e4567-e89b-12d3-a456-426614174000',
            '123e4567-e89b-12d3-a456-426614174001',
        );

        $handler($request);
    }
}
