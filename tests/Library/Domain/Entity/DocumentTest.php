<?php

declare(strict_types=1);

namespace DZunke\NovDoc\Test\Library\Domain\Entity;

use DateTimeImmutable;
use DateTimeInterface;
use DZunke\NovDoc\Library\Domain\Entity\Document;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Uuid;

use function sha1;
use function strlen;

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

    public function testFromArrayCreatesDocument(): void
    {
        $documentArr = [
            'id' => Uuid::v4()->toString(),
            'title' => 'Test Title',
            'content' => 'Test Content',
            'last_updated' => (new DateTimeImmutable())->format(DateTimeInterface::ATOM),
        ];

        $document = Document::fromArray($documentArr);

        self::assertSame($documentArr['id'], $document->id);
        self::assertSame($documentArr['title'], $document->title);
        self::assertSame($documentArr['content'], $document->content);
        self::assertSame($documentArr['last_updated'], $document->updatedAt->format(DateTimeInterface::ATOM));
    }

    public function testIsDocumentArrayReturnsTrueForValidArray(): void
    {
        $documentArr = [
            'id' => Uuid::v4()->toString(),
            'title' => 'Test Title',
            'content' => 'Test Content',
        ];

        self::assertTrue(Document::isDocumentArray($documentArr)); // @phpstan-ignore staticMethod.alreadyNarrowedType
    }

    public function testIsDocumentArrayReturnsFalseForInvalidArray(): void
    {
        $invalidArr = [
            'title' => 'Test Title',
            'content' => 'Test Content',
        ];

        self::assertFalse(Document::isDocumentArray($invalidArr)); // @phpstan-ignore staticMethod.impossibleType
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
