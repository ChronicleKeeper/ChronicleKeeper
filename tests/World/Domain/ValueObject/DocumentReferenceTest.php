<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\World\Domain\ValueObject;

use ChronicleKeeper\Test\Document\Domain\Entity\DocumentBuilder;
use ChronicleKeeper\Test\World\Domain\Entity\ItemBuilder;
use ChronicleKeeper\World\Domain\ValueObject\DocumentReference;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(DocumentReference::class)]
#[Small]
final class DocumentReferenceTest extends TestCase
{
    #[Test]
    public function itIsConstructable(): void
    {
        $item      = (new ItemBuilder())->build();
        $document  = (new DocumentBuilder())->build();
        $reference = new DocumentReference($item, $document);

        self::assertSame($item, $reference->item);
        self::assertSame($document, $reference->document);
    }

    #[Test]
    public function itReturnsCorrectType(): void
    {
        $item      = (new ItemBuilder())->build();
        $document  = (new DocumentBuilder())->build();
        $reference = new DocumentReference($item, $document);

        self::assertSame('document', $reference->getType());
    }

    #[Test]
    public function itReturnsCorrectIcon(): void
    {
        $item      = (new ItemBuilder())->build();
        $document  = (new DocumentBuilder())->build();
        $reference = new DocumentReference($item, $document);

        self::assertSame('tabler:file', $reference->getIcon());
    }

    #[Test]
    public function itReturnsCorrectMediaId(): void
    {
        $item      = (new ItemBuilder())->build();
        $document  = (new DocumentBuilder())->build();
        $reference = new DocumentReference($item, $document);

        self::assertSame($document->getId(), $reference->getMediaId());
    }

    #[Test]
    public function itReturnsCorrectMediaTitle(): void
    {
        $item      = (new ItemBuilder())->build();
        $document  = (new DocumentBuilder())->build();
        $reference = new DocumentReference($item, $document);

        self::assertSame($document->getTitle(), $reference->getMediaTitle());
    }

    #[Test]
    public function itReturnsCorrectMediaDisplayName(): void
    {
        $item      = (new ItemBuilder())->build();
        $document  = (new DocumentBuilder())->build();
        $reference = new DocumentReference($item, $document);

        self::assertSame('Hauptverzeichnis > Default Title', $reference->getMediaDisplayName());
    }

    #[Test]
    public function itReturnsCorrectGenericLinkIdentifier(): void
    {
        $item      = (new ItemBuilder())->build();
        $document  = (new DocumentBuilder())->withId('document-id')->build();
        $reference = new DocumentReference($item, $document);

        self::assertSame('document_' . $document->getId(), $reference->getGenericLinkIdentifier());
    }
}
