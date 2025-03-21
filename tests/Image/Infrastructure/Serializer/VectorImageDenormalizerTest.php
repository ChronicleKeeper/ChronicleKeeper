<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Image\Infrastructure\Serializer;

use ChronicleKeeper\Image\Application\Query\GetImage;
use ChronicleKeeper\Image\Infrastructure\Serializer\VectorImageDenormalizer;
use ChronicleKeeper\Library\Infrastructure\VectorStorage\VectorImage;
use ChronicleKeeper\Shared\Application\Query\QueryService;
use ChronicleKeeper\Test\Image\Domain\Entity\ImageBuilder;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(VectorImageDenormalizer::class)]
#[Small]
class VectorImageDenormalizerTest extends TestCase
{
    #[Test]
    public function correctSupportedTypes(): void
    {
        $denormalizer = new VectorImageDenormalizer(self::createStub(QueryService::class));

        self::assertTrue($denormalizer->supportsDenormalization([], VectorImage::class));
        self::assertFalse($denormalizer->supportsDenormalization([], 'foo'));
    }

    #[Test]
    public function deliveredSupportedTypesAreCorrect(): void
    {
        $denormalizer = new VectorImageDenormalizer(self::createStub(QueryService::class));

        self::assertSame([VectorImage::class => true], $denormalizer->getSupportedTypes(null));
    }

    #[Test]
    public function isFailingWithNonArrayData(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Expected an array. Got: string');

        $queryService = $this->createMock(QueryService::class);
        $queryService->expects($this->never())->method('query');

        (new VectorImageDenormalizer($queryService))->denormalize('foo', VectorImage::class);
    }

    #[Test]
    public function isFailingWithMissingKeys(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Expected a value identical to array. Got: array');

        $queryService = $this->createMock(QueryService::class);
        $queryService->expects($this->never())->method('query');

        (new VectorImageDenormalizer($queryService))->denormalize([], VectorImage::class);
    }

    #[Test]
    public function isDeliveringConvertedJson(): void
    {
        $array = [
            'id' => 'b3907fc7-00ec-4223-9d36-13a17080ae5a',
            'imageId' => 'e7b9b163-aa0e-4dae-8351-0a86cfb00d70',
            'content' => 'foo',
            'vectorContentHash' => 'bar',
            'vector' => [10.2],
        ];

        $queryService = $this->createMock(QueryService::class);
        $queryService->expects($this->once())
            ->method('query')
            ->with(self::equalTo(new GetImage('e7b9b163-aa0e-4dae-8351-0a86cfb00d70')))
            ->willReturn($image = (new ImageBuilder())->build());

        $vectorImage = (new VectorImageDenormalizer($queryService))->denormalize($array, VectorImage::class);

        self::assertSame('b3907fc7-00ec-4223-9d36-13a17080ae5a', $vectorImage->id);
        self::assertSame($image, $vectorImage->image);
        self::assertSame('foo', $vectorImage->content);
        self::assertSame('bar', $vectorImage->vectorContentHash);
        self::assertSame([10.2], $vectorImage->vector);
    }

    #[Test]
    public function isDeliveringConvertedJsonFromCache(): void
    {
        $array = [
            'id' => 'b3907fc7-00ec-4223-9d36-13a17080ae5a',
            'imageId' => 'b1d73186-dad8-4dcc-93f8-4b10ba0ab7f4',
            'content' => 'foo',
            'vectorContentHash' => 'bar',
            'vector' => [10.2],
        ];

        $queryService = $this->createMock(QueryService::class);
        $queryService->expects($this->once())
            ->method('query')
            ->with(self::equalTo(new GetImage('b1d73186-dad8-4dcc-93f8-4b10ba0ab7f4')))
            ->willReturn($image = (new ImageBuilder())->build());

        $denormalizer      = new VectorImageDenormalizer($queryService);
        $vectorImage       = $denormalizer->denormalize($array, VectorImage::class);
        $cachedVectorImage = $denormalizer->denormalize($array, VectorImage::class);

        self::assertSame($vectorImage, $cachedVectorImage);
    }
}
