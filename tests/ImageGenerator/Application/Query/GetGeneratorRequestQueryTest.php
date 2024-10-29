<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\ImageGenerator\Application\Query;

use ChronicleKeeper\ImageGenerator\Application\Query\GetGeneratorRequest;
use ChronicleKeeper\ImageGenerator\Application\Query\GetGeneratorRequestQuery;
use ChronicleKeeper\ImageGenerator\Domain\Entity\GeneratorRequest;
use ChronicleKeeper\Shared\Infrastructure\Persistence\Filesystem\Contracts\FileAccess;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\SerializerInterface;
use Webmozart\Assert\InvalidArgumentException;

#[CoversClass(GetGeneratorRequestQuery::class)]
#[CoversClass(GetGeneratorRequest::class)]
#[Small]
class GetGeneratorRequestQueryTest extends TestCase
{
    #[Test]
    public function correctQueryClassIsliked(): void
    {
        self::assertSame(GetGeneratorRequestQuery::class, (new GetGeneratorRequest('b06bd1f2-f7bb-43ca-948e-7fe38956667e'))->getQueryClass());
    }

    #[Test]
    public function anIdentifierHaveToBeAnUuid(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Value "foo-bar-baz" is not a valid UUID.');

        new GetGeneratorRequest('foo-bar-baz');
    }

    #[Test]
    public function theGeneratorRequestIsBuild(): void
    {
        $identifier = '4a1edd39-2787-4833-854b-12db27479efb';

        $fileAccess = $this->createMock(FileAccess::class);
        $fileAccess
            ->expects($this->once())
            ->method('read')
            ->willReturnCallback(static function (string $type, string $filename) use ($identifier): string {
                self::assertSame('generator.requests', $type);
                self::assertSame($identifier . '.json', $filename);

                return '{}';
            });

        $serializer = $this->createMock(SerializerInterface::class);
        $serializer
            ->expects($this->once())
            ->method('deserialize')
            ->willReturnCallback(static function (string $fileContent, string $target): GeneratorRequest {
                self::assertSame('{}', $fileContent);
                self::assertSame(GeneratorRequest::class, $target);

                return self::createStub(GeneratorRequest::class);
            });

        (new GetGeneratorRequestQuery($fileAccess, $serializer))
            ->query(new GetGeneratorRequest($identifier));
    }
}
