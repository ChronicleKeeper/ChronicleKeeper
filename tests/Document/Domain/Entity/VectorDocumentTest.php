<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Document\Domain\Entity;

use ChronicleKeeper\Document\Domain\Entity\VectorDocument;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

use function json_encode;

use const JSON_THROW_ON_ERROR;

#[CoversClass(VectorDocument::class)]
#[Small]
class VectorDocumentTest extends TestCase
{
    #[Test]
    public function isConstructable(): void
    {
        $document       = (new DocumentBuilder())->build();
        $vectorDocument = new VectorDocument(
            $document,
            'foo',
            'bar',
            [1.0, 2.0, 3.0],
        );

        self::assertSame($document, $vectorDocument->document);
        self::assertSame('foo', $vectorDocument->content);
        self::assertSame('bar', $vectorDocument->vectorContentHash);
        self::assertSame([1.0, 2.0, 3.0], $vectorDocument->vector);
    }

    #[Test]
    public function canBeConvertedToArray(): void
    {
        $document       = (new DocumentBuilder())->build();
        $vectorDocument = new VectorDocument(
            $document,
            'foo',
            'bar',
            [1.0, 2.0, 3.0],
        );

        self::assertSame(
            [
                'id' => $vectorDocument->id,
                'documentId' => $document->getId(),
                'content' => 'foo',
                'vectorContentHash' => 'bar',
                'vector' => [1.0, 2.0, 3.0],
            ],
            $vectorDocument->jsonSerialize(),
        );
    }

    #[Test]
    public function canBeJsonSerialized(): void
    {
        $document       = (new DocumentBuilder())->withId('7b482dcd-5cd0-4d0b-972a-30bddb01cdfd')->build();
        $vectorDocument = new VectorDocument(
            $document,
            'foo',
            'bar',
            [1.0, 2.0, 3.0],
        );

        self::assertSame(
            json_encode(
                [
                    'id' => $vectorDocument->id,
                    'documentId' => $document->getId(),
                    'content' => 'foo',
                    'vectorContentHash' => 'bar',
                    'vector' => [1.0, 2.0, 3.0],
                ],
                JSON_THROW_ON_ERROR,
            ),
            json_encode($vectorDocument, JSON_THROW_ON_ERROR),
        );
    }

    #[Test]
    public function canBeConvertedToSearchVector(): void
    {
        $document       = (new DocumentBuilder())->build();
        $vectorDocument = new VectorDocument(
            $document,
            'foo',
            'bar',
            [1.0, 2.0, 3.0],
        );

        $searchVector = $vectorDocument->toSearchVector();

        self::assertSame($vectorDocument->id, $searchVector->id);
        self::assertSame($document->getId(), $searchVector->documentId);
        self::assertSame([1.0, 2.0, 3.0], $searchVector->vectors);
    }
}
