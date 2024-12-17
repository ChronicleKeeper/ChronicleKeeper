<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Chat\Domain\ValueObject;

use ChronicleKeeper\Chat\Domain\ValueObject\Reference;
use ChronicleKeeper\Test\Document\Domain\Entity\DocumentBuilder;
use ChronicleKeeper\Test\Library\Domain\Entity\ImageBuilder;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(Reference::class)]
#[Small]
class ReferenceTest extends TestCase
{
    #[Test]
    public function itCanBeConstructed(): void
    {
        $reference = new Reference('id', 'type', 'title');

        self::assertSame('type', $reference->type);
        self::assertSame('id', $reference->id);
        self::assertSame('title', $reference->title);
    }

    #[Test]
    public function itCanBeConstructedWithADocument(): void
    {
        $document  = (new DocumentBuilder())->build();
        $reference = Reference::forDocument($document);

        self::assertSame('document', $reference->type);
        self::assertSame($document->id, $reference->id);
        self::assertSame($document->title, $reference->title);
    }

    #[Test]
    public function itCanBeConstructedWithAnImage(): void
    {
        $image     = (new ImageBuilder())->build();
        $reference = Reference::forImage($image);

        self::assertSame('image', $reference->type);
        self::assertSame($image->id, $reference->id);
        self::assertSame($image->title, $reference->title);
    }
}
