<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\ImageGenerator\Application\Query;

use ChronicleKeeper\ImageGenerator\Application\Query\FindAllImagesOfRequest;
use ChronicleKeeper\ImageGenerator\Application\Query\FindAllImagesOfRequestQuery;
use ChronicleKeeper\ImageGenerator\Domain\Entity\GeneratorResult;
use ChronicleKeeper\Shared\Infrastructure\Persistence\Filesystem\Contracts\FileAccess;
use ChronicleKeeper\Shared\Infrastructure\Persistence\Filesystem\Contracts\Finder;
use ChronicleKeeper\Shared\Infrastructure\Persistence\Filesystem\PathRegistry;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use SplFileInfo;
use Symfony\Component\Serializer\SerializerInterface;
use Webmozart\Assert\InvalidArgumentException;

#[CoversClass(FindAllImagesOfRequestQuery::class)]
#[CoversClass(FindAllImagesOfRequest::class)]
#[Small]
class FindAllImagesOfRequestQueryTest extends TestCase
{
    #[Test]
    public function correctQueryClassIsliked(): void
    {
        self::assertSame(
            FindAllImagesOfRequestQuery::class,
            (new FindAllImagesOfRequest('b06bd1f2-f7bb-43ca-948e-7fe38956667e'))->getQueryClass(),
        );
    }

    #[Test]
    public function aRequestIdentifierHaveToBeAnUuid(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Value "foo-bar-baz" is not a valid UUID.');

        new FindAllImagesOfRequest('foo-bar-baz');
    }

    #[Test]
    public function theImagesArrayIsBuild(): void
    {
        $requestId = 'b06bd1f2-f7bb-43ca-948e-7fe38956667e';

        $pathRegistry = $this->createMock(PathRegistry::class);
        $pathRegistry
            ->expects($this->once())
            ->method('get')
            ->willReturnCallback(static function ($type): string {
                self::assertSame('generator.images', $type);

                return 'root';
            });

        $finder = $this->createMock(Finder::class);
        $finder
            ->expects($this->once())
            ->method('findFilesInDirectoryOrderedByAccessTimestamp')
            ->willReturnCallback(static function (string $path) use ($requestId): array {
                self::assertSame('root/' . $requestId, $path);

                $file1 = self::createStub(SplFileInfo::class);
                $file1->method('getFilename')->willReturn('foo');

                $file2 = self::createStub(SplFileInfo::class);
                $file2->method('getFilename')->willReturn('bar');

                return [
                    $file1,
                    $file2,
                ];
            });

        $fileAccess        = $this->createMock(FileAccess::class);
        $fileAccessInvoker = $this->exactly(2);
        $fileAccess
            ->expects($fileAccessInvoker)
            ->method('read')
            ->willReturnCallback(
                static function (string $type, string $filename) use ($fileAccessInvoker, $requestId): string {
                    self::assertSame('generator.images', $type);

                    if ($fileAccessInvoker->numberOfInvocations() === 1) {
                        self::assertSame('/' . $requestId . '/foo', $filename);

                        return '{"foo": true}';
                    }

                    self::assertSame('/' . $requestId . '/bar', $filename);

                    return '{"bar": true}';
                },
            );

        $serializer        = $this->createMock(SerializerInterface::class);
        $serializerInvoker = $this->exactly(2);
        $serializer
            ->expects($serializerInvoker)
            ->method('deserialize')
            ->willReturnCallback(
                static function (string $fileContent, string $target) use ($serializerInvoker): GeneratorResult {
                    self::assertSame(GeneratorResult::class, $target);

                    match ($serializerInvoker->numberOfInvocations()) {
                        1 => self::assertSame('{"foo": true}', $fileContent),
                        3 => self::assertSame('{"bar": true}', $fileContent),
                        default => 'Kind'
                    };

                    return self::createStub(GeneratorResult::class);
                },
            );

        $response = (new FindAllImagesOfRequestQuery($pathRegistry, $fileAccess, $serializer, $finder))
            ->query(new FindAllImagesOfRequest($requestId));

        self::assertCount(2, $response);
    }
}
