<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Document\Domain\Event;

use ChronicleKeeper\Document\Domain\Entity\Document;
use ChronicleKeeper\Document\Domain\Event\DocumentRenamed;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\TestCase;

#[CoversClass(DocumentRenamed::class)]
#[Small]
final class DocumentRenamedTest extends TestCase
{
    public function testCreation(): void
    {
        $document = $this->createMock(Document::class);
        $oldTitle = 'Old Title';

        $event = new DocumentRenamed($document, $oldTitle);

        self::assertSame($document, $event->document);
        self::assertSame($oldTitle, $event->oldTitle);
    }
}
