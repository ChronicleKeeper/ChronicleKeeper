<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\ImageGenerator\Application\Command;

use ChronicleKeeper\ImageGenerator\Application\Command\StoreGeneratorResult;
use ChronicleKeeper\ImageGenerator\Application\Command\StoreGeneratorResultHandler;
use ChronicleKeeper\ImageGenerator\Application\Service\PromptOptimizer;
use ChronicleKeeper\Shared\Infrastructure\Persistence\Filesystem\Contracts\FileAccess;
use ChronicleKeeper\Test\ImageGenerator\Domain\Entity\GeneratorResultBuilder;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\SerializerInterface;
use Webmozart\Assert\InvalidArgumentException;

use const DIRECTORY_SEPARATOR;
use const JSON_PRETTY_PRINT;
use const JSON_UNESCAPED_UNICODE;

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
        $fileAccess      = $this->createMock(FileAccess::class);
        $serializer      = $this->createMock(SerializerInterface::class);
        $promptOptimizer = $this->createMock(PromptOptimizer::class);

        $handler = new StoreGeneratorResultHandler($fileAccess, $serializer, $promptOptimizer);
        $request = new StoreGeneratorResult(
            '67e4e2a2-8afa-4788-83aa-53507cf39e00',
            (new GeneratorResultBuilder())->build(),
        );

        $serializedData = '{"id":"result-id","data":"some data"}';
        $serializer->expects($this->once())
            ->method('serialize')
            ->with(
                $request->generatorResult,
                'json',
                ['json_encode_options' => JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT],
            )
            ->willReturn($serializedData);

        $fileAccess->expects($this->once())
            ->method('write')
            ->with(
                'generator.images',
                DIRECTORY_SEPARATOR . $request->requestId . DIRECTORY_SEPARATOR . $request->generatorResult->id . '.json',
                $serializedData,
            );

        $handler($request);
    }
}
