<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Library\Domain\Entity;

use ChronicleKeeper\Library\Domain\Entity\Document;
use ChronicleKeeper\Library\Domain\RootDirectory;
use DateTimeImmutable;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

use function sha1;
use function strlen;

#[CoversClass(Document::class)]
#[UsesClass(RootDirectory::class)]
#[Small]
class DocumentTest extends TestCase
{
    public function testConstructorInitializesProperties(): void
    {
        $title    = 'Test Title';
        $content  = 'Test Content';
        $document = new Document($title, $content);

        self::assertSame($title, $document->title);
        self::assertSame($content, $document->content);
        self::assertNotEmpty($document->id);
        self::assertInstanceOf(DateTimeImmutable::class, $document->updatedAt);
    }

    public function testToArrayReturnsCorrectArray(): void
    {
        $title       = 'Test Title';
        $content     = 'Test Content';
        $document    = new Document($title, $content);
        $documentArr = $document->toArray();

        self::assertSame($document->id, $documentArr['id']);
        self::assertSame($title, $documentArr['title']);
        self::assertSame($content, $documentArr['content']);

        self::assertArrayHasKey('last_updated', $documentArr);
        self::assertSame(
            $document->updatedAt->format(DateTimeImmutable::ATOM),
            $documentArr['last_updated'], // @phpstan-ignore offsetAccess.notFound
        );
    }

    public function testGetSizeReturnsCorrectLength(): void
    {
        $content  = 'Test Content';
        $document = new Document('Test Title', $content);

        self::assertSame(strlen($content), $document->getSize());
    }

    public function testGetContentHashReturnsCorrectHash(): void
    {
        $content  = 'Test Content';
        $document = new Document('Test Title', $content);

        self::assertSame(sha1($content), $document->getContentHash());
    }
}
