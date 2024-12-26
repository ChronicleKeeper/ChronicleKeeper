<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Document\Domain\Event;

use ChronicleKeeper\Document\Domain\Entity\Document;
use ChronicleKeeper\Document\Domain\Event\DocumentMovedToDirectory;
use ChronicleKeeper\Library\Domain\Entity\Directory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\TestCase;

#[CoversClass(DocumentMovedToDirectory::class)]
#[Small]
final class DocumentMovedToDirectoryTest extends TestCase
{
    public function testCreation(): void
    {
        $document     = $this->createMock(Document::class);
        $oldDirectory = $this->createMock(Directory::class);

        $event = new DocumentMovedToDirectory($document, $oldDirectory);

        self::assertSame($document, $event->document);
        self::assertSame($oldDirectory, $event->oldDirectory);
    }
}
