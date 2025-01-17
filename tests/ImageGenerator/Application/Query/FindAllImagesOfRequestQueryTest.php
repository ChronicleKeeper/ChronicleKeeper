<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\ImageGenerator\Application\Query;

use ChronicleKeeper\ImageGenerator\Application\Query\FindAllImagesOfRequest;
use ChronicleKeeper\ImageGenerator\Application\Query\FindAllImagesOfRequestQuery;
use ChronicleKeeper\ImageGenerator\Domain\Entity\GeneratorResult;
use ChronicleKeeper\Shared\Infrastructure\Database\DatabasePlatform;
use ChronicleKeeper\Test\ImageGenerator\Domain\Entity\GeneratorResultBuilder;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
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

        $databasePlatform = $this->createMock(DatabasePlatform::class);
        $databasePlatform->expects($this->once())
            ->method('fetch')
            ->with('SELECT * FROM generator_results WHERE generatorRequest = :id', ['id' => $requestId])
            ->willReturn([
                ['title' => 'Image 1'],
                ['title' => 'Image 2'],
            ]);

        $denormalizer        = $this->createMock(DenormalizerInterface::class);
        $denormalizerInvoker = $this->exactly(2);
        $denormalizer
            ->expects($denormalizerInvoker)
            ->method('denormalize')
            ->willReturnCallback(
                static function (array $data, string $target) use ($denormalizerInvoker): GeneratorResult {
                    self::assertSame(GeneratorResult::class, $target);

                    if ($denormalizerInvoker->numberOfInvocations() === 1) {
                        self::assertSame('Image 1', $data['title']);

                        return (new GeneratorResultBuilder())->build();
                    }

                    self::assertSame('Image 2', $data['title']);

                    return (new GeneratorResultBuilder())->build();
                },
            );

        $response = (new FindAllImagesOfRequestQuery($denormalizer, $databasePlatform))
            ->query(new FindAllImagesOfRequest($requestId));

        self::assertCount(2, $response);
    }
}
