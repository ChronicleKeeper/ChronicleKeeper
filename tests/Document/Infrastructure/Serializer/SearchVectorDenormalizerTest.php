<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Document\Infrastructure\Serializer;

use ChronicleKeeper\Document\Domain\Entity\SearchVector;
use ChronicleKeeper\Document\Infrastructure\Serializer\SearchVectorDenormalizer;
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
    public function isFailingWithMissingDocumentIdKey(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Expected the key "documentId" to exist.');

        (new SearchVectorDenormalizer())->denormalize(['id' => '123'], SearchVector::class);
    }

    #[Test]
    public function isFailingWithMissingVectorKey(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Expected the key "vector" to exist.');

        (new SearchVectorDenormalizer())->denormalize(['id' => '123', 'documentId' => '456'], SearchVector::class);
    }

    #[Test]
    public function isDeliveringConvertedJson(): void
    {
        $array = [
            'id' => '123',
            'documentId' => '456',
            'content' => 'foo',
            'vectorContentHash' => 'bar',
            'vector' => [10.2],
        ];

        $vectorDocument = (new SearchVectorDenormalizer())
            ->denormalize($array, SearchVector::class);

        self::assertSame('123', $vectorDocument->id);
        self::assertSame('456', $vectorDocument->documentId);
        self::assertSame([10.2], $vectorDocument->vectors);
    }
}
