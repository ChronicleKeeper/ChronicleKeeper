<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Document\Domain\Event;

use ChronicleKeeper\Document\Domain\Entity\Document;
use ChronicleKeeper\Document\Domain\Event\DocumentChangedContent;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\TestCase;

#[CoversClass(DocumentChangedContent::class)]
#[Small]
final class DocumentChangedContentTest extends TestCase
{
    public function testCreation(): void
    {
        $document   = $this->createMock(Document::class);
        $oldContent = 'Old Content';

        $event = new DocumentChangedContent($document, $oldContent);

        self::assertSame($document, $event->document);
        self::assertSame($oldContent, $event->oldContent);
    }
}
