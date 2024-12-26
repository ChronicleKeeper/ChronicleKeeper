<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Document\Domain\Entity;

use ChronicleKeeper\Document\Domain\Entity\SearchVector;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(SearchVector::class)]
#[Small]
class SearchVectorTest extends TestCase
{
    #[Test]
    public function isConstructable(): void
    {
        $searchVector = new SearchVector(
            'foo',
            'bar',
            [1.0, 2.0, 3.0],
        );

        self::assertSame('foo', $searchVector->id);
        self::assertSame('bar', $searchVector->documentId);
        self::assertSame([1.0, 2.0, 3.0], $searchVector->vectors);
    }

    #[Test]
    public function canBeCreatedFromVectorDocument(): void
    {
        $vectorDocument = (new VectorDocumentBuilder())->build();
        $searchVector   = SearchVector::fromVectorDocument($vectorDocument);

        self::assertSame($vectorDocument->id, $searchVector->id);
        self::assertSame($vectorDocument->document->getId(), $searchVector->documentId);
        self::assertSame($vectorDocument->vector, $searchVector->vectors);
    }
}
