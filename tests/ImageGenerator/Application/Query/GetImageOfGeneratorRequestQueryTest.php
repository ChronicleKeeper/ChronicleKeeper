<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\ImageGenerator\Application\Query;

use ChronicleKeeper\ImageGenerator\Application\Query\GetImageOfGeneratorRequest;
use ChronicleKeeper\ImageGenerator\Application\Query\GetImageOfGeneratorRequestQuery;
use ChronicleKeeper\ImageGenerator\Domain\Entity\GeneratorResult;
use ChronicleKeeper\Shared\Infrastructure\Persistence\Filesystem\Contracts\FileAccess;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\SerializerInterface;
use Webmozart\Assert\InvalidArgumentException;

#[CoversClass(GetImageOfGeneratorRequestQuery::class)]
#[CoversClass(GetImageOfGeneratorRequest::class)]
#[Small]
class GetImageOfGeneratorRequestQueryTest extends TestCase
{
    #[Test]
    public function correctQueryClassIsliked(): void
    {
        self::assertSame(GetImageOfGeneratorRequestQuery::class, (new GetImageOfGeneratorRequest(
            '5b3cde06-bc8b-4389-8407-2493a58d95e7',
            'b06bd1f2-f7bb-43ca-948e-7fe38956667e',
        ))->getQueryClass());
    }

    #[Test]
    public function aRequestIdentifierHaveToBeAUuid(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Value "foo-bar-baz" is not a valid UUID.');

        new GetImageOfGeneratorRequest(
            'foo-bar-baz',
            '51b7e687-309a-4ee4-9d11-d6fbd977acde',
        );
    }

    #[Test]
    public function anImageIdentifierHaveToBeAUuid(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Value "foo-bar-baz" is not a valid UUID.');

        new GetImageOfGeneratorRequest(
            '51b7e687-309a-4ee4-9d11-d6fbd977acde',
            'foo-bar-baz',
        );
    }

    #[Test]
    public function theGeneratorResultIsBuild(): void
    {
        $requestId = '5b3cde06-bc8b-4389-8407-2493a58d95e7';
        $imageId   = '51b7e687-309a-4ee4-9d11-d6fbd977acde';

        $fileAccess = $this->createMock(FileAccess::class);
        $fileAccess
            ->expects($this->once())
            ->method('read')
            ->willReturnCallback(static function (string $type, string $filename) use ($requestId, $imageId): string {
                self::assertSame('generator.images', $type);
                self::assertSame($requestId . '/' . $imageId . '.json', $filename);

                return '{}';
            });

        $serializer = $this->createMock(SerializerInterface::class);
        $serializer
            ->expects($this->once())
            ->method('deserialize')
            ->willReturnCallback(static function (string $fileContent, string $target): GeneratorResult {
                self::assertSame('{}', $fileContent);
                self::assertSame(GeneratorResult::class, $target);

                return self::createStub(GeneratorResult::class);
            });

        (new GetImageOfGeneratorRequestQuery($fileAccess, $serializer))
            ->query(new GetImageOfGeneratorRequest($requestId, $imageId));
    }
}
