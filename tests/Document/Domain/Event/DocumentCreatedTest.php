<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Document\Domain\Event;

use ChronicleKeeper\Document\Domain\Entity\Document;
use ChronicleKeeper\Document\Domain\Event\DocumentCreated;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\TestCase;

#[CoversClass(DocumentCreated::class)]
#[Small]
final class DocumentCreatedTest extends TestCase
{
    public function testCreation(): void
    {
        $document = $this->createMock(Document::class);

        $event = new DocumentCreated($document);

        self::assertSame($document, $event->document);
    }
}
