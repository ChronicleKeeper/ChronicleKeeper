<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Library\Domain\Entity;

use ChronicleKeeper\Library\Domain\Entity\Document;
use DateTimeInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

use function sha1;
use function strlen;

#[CoversClass(Document::class)]
#[Small]
class DocumentTest extends TestCase
{
    #[Test]
    public function constructorInitializesProperties(): void
    {
        $title    = 'Test Title';
        $content  = 'Test Content';
        $document = new Document($title, $content);

        self::assertSame($title, $document->title);
        self::assertSame($content, $document->content);
        self::assertNotEmpty($document->id);
    }

    #[Test]
    public function toArrayReturnsCorrectArray(): void
    {
        $title       = 'Test Title';
        $content     = 'Test Content';
        $document    = new Document($title, $content);
        $documentArr = $document->toArray();

        self::assertSame($document->id, $documentArr['id']);
        self::assertSame($title, $documentArr['title']);
        self::assertSame($content, $documentArr['content']);

        self::assertArrayHasKey('last_updated', $documentArr);
        self::assertSame($document->updatedAt->format(DateTimeInterface::ATOM), $documentArr['last_updated']);
    }

    #[Test]
    public function getSizeReturnsCorrectLength(): void
    {
        $content  = 'Test Content';
        $document = new Document('Test Title', $content);

        self::assertSame(strlen($content), $document->getSize());
    }

    #[Test]
    public function getContentHashReturnsCorrectHash(): void
    {
        $content  = 'Test Content';
        $document = new Document('Test Title', $content);

        self::assertSame(sha1($content), $document->getContentHash());
    }
}
