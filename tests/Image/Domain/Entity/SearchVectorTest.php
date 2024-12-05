<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Image\Domain\Entity;

use ChronicleKeeper\Image\Domain\Entity\SearchVector;
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
        self::assertSame('bar', $searchVector->imageId);
        self::assertSame([1.0, 2.0, 3.0], $searchVector->vectors);
    }

    #[Test]
    public function canBeCreatedFromVectorImage(): void
    {
        $vectorImage  = (new VectorImageBuilder())->build();
        $searchVector = SearchVector::formVectorImage($vectorImage);

        self::assertSame($vectorImage->id, $searchVector->id);
        self::assertSame($vectorImage->image->id, $searchVector->imageId);
        self::assertSame($vectorImage->vector, $searchVector->vectors);
    }
}
