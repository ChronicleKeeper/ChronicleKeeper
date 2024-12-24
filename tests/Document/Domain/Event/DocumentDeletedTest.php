<?php

declare(strict_types=1);

namespace ChronicleKeeper\Tests\Document\Domain\Event;

use ChronicleKeeper\Document\Domain\Event\DocumentDeleted;
use ChronicleKeeper\Test\Document\Domain\Entity\DocumentBuilder;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(DocumentDeleted::class)]
#[Small]
class DocumentDeletedTest extends TestCase
{
    #[Test]
    public function isIsCreatable(): void
    {
        $document = (new DocumentBuilder())->build();
        $event    = new DocumentDeleted($document);

        self::assertSame($document, $event->document);
    }
}
