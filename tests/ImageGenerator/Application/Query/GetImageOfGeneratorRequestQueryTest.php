<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\ImageGenerator\Application\Query;

use ChronicleKeeper\ImageGenerator\Application\Query\GetImageOfGeneratorRequest;
use ChronicleKeeper\ImageGenerator\Application\Query\GetImageOfGeneratorRequestQuery;
use ChronicleKeeper\ImageGenerator\Domain\Entity\GeneratorResult;
use ChronicleKeeper\Shared\Infrastructure\Database\DatabasePlatform;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
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

        $databasePlatform = $this->createMock(DatabasePlatform::class);
        $databasePlatform->expects($this->once())
            ->method('fetch')
            ->with(
                'SELECT * FROM generator_results WHERE generatorRequest = :requestId AND id = :imageId',
                ['requestId' => $requestId, 'imageId' => $imageId],
            )
            ->willReturn([['title' => 'foo']]);

        $denormalizer = $this->createMock(DenormalizerInterface::class);
        $denormalizer
            ->expects($this->once())
            ->method('denormalize')
            ->willReturnCallback(static function (array $data, string $target): GeneratorResult {
                self::assertSame(GeneratorResult::class, $target);

                self::assertSame('foo', $data['title']);

                return self::createStub(GeneratorResult::class);
            });

        (new GetImageOfGeneratorRequestQuery($denormalizer, $databasePlatform))
            ->query(new GetImageOfGeneratorRequest($requestId, $imageId));
    }
}
