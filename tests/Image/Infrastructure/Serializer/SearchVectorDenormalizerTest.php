<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Image\Infrastructure\Serializer;

use ChronicleKeeper\Image\Domain\Entity\SearchVector;
use ChronicleKeeper\Image\Infrastructure\Serializer\SearchVectorDenormalizer;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(SearchVectorDenormalizer::class)]
#[Small]
class SearchVectorDenormalizerTest extends TestCase
{
    #[Test]
    public function correctSupportedTypes(): void
    {
        $denormalizer = new SearchVectorDenormalizer();

        self::assertTrue($denormalizer->supportsDenormalization([], SearchVector::class));
        self::assertFalse($denormalizer->supportsDenormalization([], 'foo'));
    }

    #[Test]
    public function deliveredSupportedTypesAreCorrect(): void
    {
        $denormalizer = new SearchVectorDenormalizer();

        self::assertSame([SearchVector::class => true], $denormalizer->getSupportedTypes(null));
    }

    #[Test]
    public function isFailingWithNonArrayData(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Expected an array. Got: string');

        (new SearchVectorDenormalizer())->denormalize('foo', SearchVector::class);
    }

    #[Test]
    public function isFailingWithMissingIdentifierKey(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Expected the key "id" to exist.');

        (new SearchVectorDenormalizer())->denormalize([], SearchVector::class);
    }

    #[Test]
    public function isFailingWithMissingImageIdKey(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Expected the key "imageId" to exist.');

        (new SearchVectorDenormalizer())->denormalize(['id' => '123'], SearchVector::class);
    }

    #[Test]
    public function isFailingWithMissingVectorKey(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Expected the key "vector" to exist.');

        (new SearchVectorDenormalizer())->denormalize(['id' => '123', 'imageId' => '456'], SearchVector::class);
    }

    #[Test]
    public function isDeliveringConvertedJson(): void
    {
        $array = [
            'id' => '123',
            'imageId' => '456',
            'content' => 'foo',
            'vectorContentHash' => 'bar',
            'vector' => [10.2],
        ];

        $vectorImage = (new SearchVectorDenormalizer())->denormalize($array, SearchVector::class);

        self::assertSame('123', $vectorImage->id);
        self::assertSame('456', $vectorImage->imageId);
        self::assertSame([10.2], $vectorImage->vectors);
    }
}
