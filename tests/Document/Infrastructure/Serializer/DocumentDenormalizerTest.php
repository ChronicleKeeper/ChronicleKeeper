<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Document\Infrastructure\Serializer;

use ChronicleKeeper\Document\Application\Query\GetDocument;
use ChronicleKeeper\Document\Domain\Entity\Document;
use ChronicleKeeper\Document\Infrastructure\Serializer\DocumentDenormalizer;
use ChronicleKeeper\Library\Domain\Entity\Directory;
use ChronicleKeeper\Shared\Application\Query\QueryService;
use ChronicleKeeper\Test\Document\Domain\Entity\DocumentBuilder;
use ChronicleKeeper\Test\Library\Domain\Entity\DirectoryBuilder;
use DateTimeImmutable;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

#[CoversClass(DocumentDenormalizer::class)]
#[Small]
class DocumentDenormalizerTest extends TestCase
{
    #[Test]
    public function correctSupportedTypes(): void
    {
        $denormalizer = new DocumentDenormalizer(self::createStub(QueryService::class));

        self::assertTrue($denormalizer->supportsDenormalization([], Document::class));
        self::assertFalse($denormalizer->supportsDenormalization([], 'foo'));
    }

    #[Test]
    public function deliveredSupportedTypesAreCorrect(): void
    {
        $denormalizer = new DocumentDenormalizer(self::createStub(QueryService::class));

        self::assertSame([Document::class => true], $denormalizer->getSupportedTypes(null));
    }

    #[Test]
    public function isQueryingForDocumentOnStringData(): void
    {
        $queryService = $this->createMock(QueryService::class);
        $queryService->expects($this->once())
            ->method('query')
            ->with(self::equalTo(new GetDocument('24c19e34-39c7-4c67-930f-9a7a969489c5')))
            ->willReturn($document = (new DocumentBuilder())->build());

        $denormalizer = new DocumentDenormalizer($queryService);
        $denormalizer->setDenormalizer($denormalizer);

        self::assertSame(
            $document,
            $denormalizer->denormalize('24c19e34-39c7-4c67-930f-9a7a969489c5', Document::class),
        );
    }

    #[Test]
    public function isQueryingForDocumentOnStringDataFromCache(): void
    {
        $queryService = $this->createMock(QueryService::class);
        $queryService->expects($this->once())
            ->method('query')
            ->with(self::isInstanceOf(GetDocument::class))
            ->willReturn($document = (new DocumentBuilder())->build());

        $denormalizer = new DocumentDenormalizer($queryService);
        $denormalizer->setDenormalizer($denormalizer);

        $document       = $denormalizer->denormalize($document->getId(), Document::class);
        $cachedDocument = $denormalizer->denormalize($document->getId(), Document::class);

        self::assertSame($document, $cachedDocument);
    }

    #[Test]
    public function isFailingWithNonArrayData(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Expected an array. Got: integer');

        $queryService = $this->createMock(QueryService::class);
        $queryService->expects($this->never())->method('query');

        (new DocumentDenormalizer($queryService))->denormalize(1234, Document::class);
    }

    #[Test]
    public function isFailingWithMissingKeys(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Expected a value identical to array. Got: array');

        $queryService = $this->createMock(QueryService::class);
        $queryService->expects($this->never())->method('query');

        (new DocumentDenormalizer($queryService))->denormalize([], Document::class);
    }

    #[Test]
    public function isDeliveringConvertedJson(): void
    {
        $array = [
            'id'           => 'b0b4a534-9c7b-47a0-9ef0-1f86377a6b69',
            'title'        => 'foo',
            'content'      => 'bar',
            'directory'    => 'baz',
            'last_updated' => '2021-01-01 00:00:00',
        ];

        $queryService = $this->createMock(QueryService::class);
        $queryService->expects($this->never())->method('query');

        $denormalizer = $this->createMock(DenormalizerInterface::class);
        $denormalizer->expects($this->once())
            ->method('denormalize')
            ->with(
                self::equalTo('baz'),
                self::equalTo(Directory::class),
                self::equalTo(null),
                self::equalTo([]),
            )
            ->willReturn($directory = (new DirectoryBuilder())->build());

        $documentDenormalizer = new DocumentDenormalizer($queryService);
        $documentDenormalizer->setDenormalizer($denormalizer);

        $document = $documentDenormalizer->denormalize($array, Document::class);

        self::assertSame('b0b4a534-9c7b-47a0-9ef0-1f86377a6b69', $document->getId());
        self::assertSame('foo', $document->getTitle());
        self::assertSame('bar', $document->getContent());
        self::assertSame($directory, $document->getDirectory());
        self::assertEquals(new DateTimeImmutable('2021-01-01 00:00:00'), $document->getUpdatedAt());
    }

    #[Test]
    public function isDeliveringCachedDocument(): void
    {
        $array = [
            'id'           => 'b0b4a534-9c7b-47a0-9ef0-1f86377a6b69',
            'title'        => 'foo',
            'content'      => 'bar',
            'directory'    => 'baz',
            'last_updated' => '2021-01-01 00:00:00',
        ];

        $queryService = $this->createMock(QueryService::class);
        $queryService->expects($this->never())->method('query');

        $denormalizer = $this->createMock(DenormalizerInterface::class);
        $denormalizer->expects($this->once())
            ->method('denormalize')
            ->with(
                self::equalTo('baz'),
                self::equalTo(Directory::class),
                self::equalTo(null),
                self::equalTo([]),
            )
            ->willReturn($directory = (new DirectoryBuilder())->build());

        $documentDenormalizer = new DocumentDenormalizer($queryService);
        $documentDenormalizer->setDenormalizer($denormalizer);

        $document       = $documentDenormalizer->denormalize($array, Document::class);
        $cachedDocument = $documentDenormalizer->denormalize($array, Document::class);

        self::assertSame($document, $cachedDocument);
    }
}
