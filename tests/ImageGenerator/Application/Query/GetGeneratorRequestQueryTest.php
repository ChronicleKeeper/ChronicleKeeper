<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\ImageGenerator\Application\Query;

use ChronicleKeeper\ImageGenerator\Application\Query\GetGeneratorRequest;
use ChronicleKeeper\ImageGenerator\Application\Query\GetGeneratorRequestQuery;
use ChronicleKeeper\ImageGenerator\Domain\Entity\GeneratorRequest;
use ChronicleKeeper\Shared\Infrastructure\Database\DatabasePlatform;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Webmozart\Assert\InvalidArgumentException;

#[CoversClass(GetGeneratorRequestQuery::class)]
#[CoversClass(GetGeneratorRequest::class)]
#[Small]
class GetGeneratorRequestQueryTest extends TestCase
{
    #[Test]
    public function correctQueryClassIsliked(): void
    {
        self::assertSame(
            GetGeneratorRequestQuery::class,
            (new GetGeneratorRequest('b06bd1f2-f7bb-43ca-948e-7fe38956667e'))->getQueryClass(),
        );
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

        $databasePlatform = $this->createMock(DatabasePlatform::class);
        $databasePlatform->expects($this->once())
            ->method('fetchSingleRow')
            ->with('SELECT * FROM generator_requests WHERE id = :id', ['id' => $identifier])
            ->willReturn(['title' => 'foo', 'userInput' => '{}']);

        $denormalizer = $this->createMock(DenormalizerInterface::class);
        $denormalizer
            ->expects($this->once())
            ->method('denormalize')
            ->willReturnCallback(static function (array $data, string $target): GeneratorRequest {
                self::assertSame(GeneratorRequest::class, $target);

                self::assertSame('foo', $data['title']);
                self::assertIsArray($data['userInput']);

                return self::createStub(GeneratorRequest::class);
            });

        (new GetGeneratorRequestQuery($denormalizer, $databasePlatform))
            ->query(new GetGeneratorRequest($identifier));
    }
}
