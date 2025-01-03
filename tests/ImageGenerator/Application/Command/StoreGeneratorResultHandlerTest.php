<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\ImageGenerator\Application\Command;

use ChronicleKeeper\ImageGenerator\Application\Command\StoreGeneratorResult;
use ChronicleKeeper\ImageGenerator\Application\Command\StoreGeneratorResultHandler;
use ChronicleKeeper\Shared\Infrastructure\Database\DatabasePlatform;
use ChronicleKeeper\Test\ImageGenerator\Domain\Entity\GeneratorResultBuilder;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Webmozart\Assert\InvalidArgumentException;

#[CoversClass(StoreGeneratorResultHandler::class)]
#[CoversClass(StoreGeneratorResult::class)]
#[Small]
class StoreGeneratorResultHandlerTest extends TestCase
{
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
        $databasePlatform = $this->createMock(DatabasePlatform::class);

        $handler = new StoreGeneratorResultHandler($databasePlatform);
        $request = new StoreGeneratorResult(
            '67e4e2a2-8afa-4788-83aa-53507cf39e00',
            (new GeneratorResultBuilder())->build(),
        );

        $databasePlatform->expects($this->once())
            ->method('insertOrUpdate')
            ->with(
                'generator_results',
                [
                    'id'             => $request->generatorResult->id,
                    'generatorRequest' => $request->requestId,
                    'encodedImage'   => $request->generatorResult->encodedImage,
                    'revisedPrompt'  => $request->generatorResult->revisedPrompt,
                    'mimeType'       => $request->generatorResult->mimeType,
                    'image'          => $request->generatorResult->image,
                ],
            );

        $handler($request);
    }
}
