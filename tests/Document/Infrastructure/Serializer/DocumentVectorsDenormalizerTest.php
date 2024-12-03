<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Document\Infrastructure\Serializer;

use ChronicleKeeper\Document\Application\Query\GetDocument;
use ChronicleKeeper\Document\Infrastructure\Serializer\DocumentVectorsDenormalizer;
use ChronicleKeeper\Library\Infrastructure\VectorStorage\VectorDocument;
use ChronicleKeeper\Shared\Application\Query\QueryService;
use ChronicleKeeper\Test\Library\Domain\Entity\DocumentBuilder;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(DocumentVectorsDenormalizer::class)]
#[Small]
class DocumentVectorsDenormalizerTest extends TestCase
{
    #[Test]
    public function correctSupportedTypes(): void
    {
        $denormalizer = new DocumentVectorsDenormalizer(self::createStub(QueryService::class));

        self::assertTrue($denormalizer->supportsDenormalization([], VectorDocument::class));
        self::assertFalse($denormalizer->supportsDenormalization([], 'foo'));
    }

    #[Test]
    public function deliveredSupportedTypesAreCorrect(): void
    {
        $denormalizer = new DocumentVectorsDenormalizer(self::createStub(QueryService::class));

        self::assertSame([VectorDocument::class => true], $denormalizer->getSupportedTypes(null));
    }

    #[Test]
    public function isFailingWithNonArrayData(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Expected an array. Got: string');

        $queryService = $this->createMock(QueryService::class);
        $queryService->expects($this->never())->method('query');

        (new DocumentVectorsDenormalizer($queryService))->denormalize('foo', VectorDocument::class);
    }

    #[Test]
    public function isFailingWithMissingKeys(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Expected a value identical to array. Got: array');

        $queryService = $this->createMock(QueryService::class);
        $queryService->expects($this->never())->method('query');

        (new DocumentVectorsDenormalizer($queryService))->denormalize([], VectorDocument::class);
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

        $queryService = $this->createMock(QueryService::class);
        $queryService->expects($this->once())
            ->method('query')
            ->with(self::equalTo(new GetDocument('456')))
            ->willReturn($document = (new DocumentBuilder())->build());

        $vectorDocument = (new DocumentVectorsDenormalizer($queryService))
            ->denormalize($array, VectorDocument::class);

        self::assertSame('123', $vectorDocument->id);
        self::assertSame($document, $vectorDocument->document);
        self::assertSame('foo', $vectorDocument->content);
        self::assertSame('bar', $vectorDocument->vectorContentHash);
        self::assertSame([10.2], $vectorDocument->vector);
    }
}
