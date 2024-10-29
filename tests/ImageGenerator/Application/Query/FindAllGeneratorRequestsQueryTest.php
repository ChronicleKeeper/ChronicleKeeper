<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\ImageGenerator\Application\Query;

use ChronicleKeeper\ImageGenerator\Application\Query\FindAllGeneratorRequests;
use ChronicleKeeper\ImageGenerator\Application\Query\FindAllGeneratorRequestsQuery;
use ChronicleKeeper\ImageGenerator\Domain\Entity\GeneratorRequest;
use ChronicleKeeper\Shared\Infrastructure\Persistence\Filesystem\Contracts\FileAccess;
use ChronicleKeeper\Shared\Infrastructure\Persistence\Filesystem\Contracts\Finder;
use ChronicleKeeper\Shared\Infrastructure\Persistence\Filesystem\PathRegistry;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use SplFileInfo;
use Symfony\Component\Serializer\SerializerInterface;

use function array_map;

#[CoversClass(FindAllGeneratorRequestsQuery::class)]
#[CoversClass(FindAllGeneratorRequests::class)]
#[Small]
class FindAllGeneratorRequestsQueryTest extends TestCase
{
    #[Test]
    public function correctQueryClassIsliked(): void
    {
        self::assertSame(
            FindAllGeneratorRequestsQuery::class,
            (new FindAllGeneratorRequests())->getQueryClass(),
        );
    }

    #[Test]
    public function isDeliveringFoundGeneratorRequests(): void
    {
        $pathRegistry = $this->createMock(PathRegistry::class);
        $pathRegistry
            ->expects($this->once())
            ->method('get')
            ->willReturnCallback(static function ($type): string {
                self::assertSame('generator.requests', $type);

                return '/';
            });

        $finder = $this->createMock(Finder::class);
        $finder
            ->expects($this->once())
            ->method('findFilesInDirectory')
            ->willReturnCallback(static function (string $path): array {
                self::assertSame('/', $path);

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
                static function (string $type, string $filename) use ($fileAccessInvoker): string {
                    self::assertSame('generator.requests', $type);

                    if ($fileAccessInvoker->numberOfInvocations() === 1) {
                        self::assertSame('foo', $filename);

                        return '{"foo": true}';
                    }

                    self::assertSame('bar', $filename);

                    return '{"bar": true}';
                },
            );

        $serializer        = $this->createMock(SerializerInterface::class);
        $serializerInvoker = $this->exactly(2);
        $serializer
            ->expects($serializerInvoker)
            ->method('deserialize')
            ->willReturnCallback(
                static function (string $fileContent, string $target) use ($serializerInvoker): GeneratorRequest {
                    self::assertSame(GeneratorRequest::class, $target);

                    match ($serializerInvoker->numberOfInvocations()) {
                        1 => self::assertSame('{"foo": true}', $fileContent),
                        3 => self::assertSame('{"bar": true}', $fileContent),
                        default => 'Kind'
                    };

                    $stub        = self::createStub(GeneratorRequest::class);
                    $stub->title = $fileContent;

                    return $stub;
                },
            );

        $response = (new FindAllGeneratorRequestsQuery($pathRegistry, $fileAccess, $serializer, $finder))
            ->query(new FindAllGeneratorRequests());

        self::assertCount(2, $response);
        self::assertSame(
            ['{"bar": true}', '{"foo": true}'],
            array_map(static fn (GeneratorRequest $request): string => $request->title, $response),
            'Requests are not in the correct alphabetical order',
        );
    }
}
