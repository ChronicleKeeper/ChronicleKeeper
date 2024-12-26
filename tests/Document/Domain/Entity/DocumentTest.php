<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Document\Domain\Entity;

use ChronicleKeeper\Document\Domain\Entity\Document;
use ChronicleKeeper\Document\Domain\Event\DocumentChangedContent;
use ChronicleKeeper\Document\Domain\Event\DocumentCreated;
use ChronicleKeeper\Document\Domain\Event\DocumentMovedToDirectory;
use ChronicleKeeper\Document\Domain\Event\DocumentRenamed;
use ChronicleKeeper\Library\Domain\RootDirectory;
use ChronicleKeeper\Test\Library\Domain\Entity\DirectoryBuilder;
use DateTimeImmutable;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

use function json_encode;

use const JSON_THROW_ON_ERROR;

#[CoversClass(Document::class)]
#[Small]
class DocumentTest extends TestCase
{
    #[Test]
    public function canBeCreated(): void
    {
        $now       = new DateTimeImmutable();
        $directory = RootDirectory::get();

        $document = Document::create('Test Title', 'Test Content');

        self::assertNotEmpty($document->getId());
        self::assertSame('Test Title', $document->getTitle());
        self::assertSame('Test Content', $document->getContent());
        self::assertSame($directory->getId(), $document->getDirectory()->getId());
        self::assertGreaterThanOrEqual($now, $document->getUpdatedAt());

        $events = $document->flushEvents();
        self::assertCount(1, $events);
        self::assertInstanceOf(DocumentCreated::class, $events[0]);
        self::assertSame($document, $events[0]->document);
    }

    #[Test]
    public function canBeCreatedWithCustomDirectory(): void
    {
        $directory = (new DirectoryBuilder())->build();
        $document  = Document::create('Test Title', 'Test Content', $directory);

        self::assertSame($directory, $document->getDirectory());
    }

    #[Test]
    public function canBeConstructed(): void
    {
        $now       = new DateTimeImmutable();
        $directory = RootDirectory::get();

        $document = new Document(
            'test-id',
            'Test Title',
            'Test Content',
            $directory,
            $now,
        );

        self::assertSame('test-id', $document->getId());
        self::assertSame('Test Title', $document->getTitle());
        self::assertSame('Test Content', $document->getContent());
        self::assertSame($directory, $document->getDirectory());
        self::assertSame($now, $document->getUpdatedAt());

        $events = $document->flushEvents();
        self::assertCount(0, $events);
    }

    #[Test]
    public function canBeConvertedToArray(): void
    {
        $document = (new DocumentBuilder())
            ->withId('7b482dcd-5cd0-4d0b-972a-30bddb01cdfd')
            ->withUpdatedAt(new DateTimeImmutable('2024-12-03T16:01:35+00:00'))
            ->build();

        self::assertSame(
            [
                'id' => '7b482dcd-5cd0-4d0b-972a-30bddb01cdfd',
                'title' => 'Default Title',
                'content' => 'Default Content',
                'directory' => 'caf93493-9072-44e2-a6db-4476985a849d',
                'last_updated' => '2024-12-03T16:01:35+00:00',
            ],
            $document->toArray(),
        );
    }

    #[Test]
    public function canBeJsonSerialized(): void
    {
        $document = (new DocumentBuilder())
            ->withId('7b482dcd-5cd0-4d0b-972a-30bddb01cdfd')
            ->withUpdatedAt(new DateTimeImmutable('2024-12-03T16:01:35+00:00'))
            ->build();

        self::assertSame(
            json_encode(
                [
                    'id' => '7b482dcd-5cd0-4d0b-972a-30bddb01cdfd',
                    'title' => 'Default Title',
                    'content' => 'Default Content',
                    'directory' => 'caf93493-9072-44e2-a6db-4476985a849d',
                    'last_updated' => '2024-12-03T16:01:35+00:00',
                ],
                JSON_THROW_ON_ERROR,
            ),
            json_encode($document, JSON_THROW_ON_ERROR),
        );
    }

    #[Test]
    public function canGenerateSlug(): void
    {
        $document = (new DocumentBuilder())->withTitle('foo bör baz')->build();
        self::assertSame('foo-boer-baz', $document->getSlug());
    }

    #[Test]
    public function canCalculateSize(): void
    {
        $document = (new DocumentBuilder())->withContent('foo bar baz')->build();
        self::assertSame(11, $document->getSize());
    }

    #[Test]
    public function canCalculateContentHash(): void
    {
        $document = (new DocumentBuilder())->withContent('foo bar baz')->build();
        self::assertSame('c7567e8b39e2428e38bf9c9226ac68de4c67dc39', $document->getContentHash());
    }

    #[Test]
    public function canMoveToDirectory(): void
    {
        $document     = (new DocumentBuilder())->build();
        $newDirectory = (new DirectoryBuilder())->build();
        $now          = new DateTimeImmutable();

        $document->moveToDirectory($newDirectory);

        self::assertSame($newDirectory, $document->getDirectory());
        self::assertGreaterThanOrEqual($now, $document->getUpdatedAt());

        $events = $document->flushEvents();
        self::assertCount(1, $events);
        self::assertInstanceOf(DocumentMovedToDirectory::class, $events[0]);
        self::assertSame($document, $events[0]->document);
    }

    #[Test]
    public function canBeRenamed(): void
    {
        $document = (new DocumentBuilder())->build();
        $now      = new DateTimeImmutable();

        $document->rename('New Title');

        self::assertSame('New Title', $document->getTitle());
        self::assertGreaterThanOrEqual($now, $document->getUpdatedAt());

        $events = $document->flushEvents();
        self::assertCount(1, $events);
        self::assertInstanceOf(DocumentRenamed::class, $events[0]);
        self::assertSame($document, $events[0]->document);
    }

    #[Test]
    public function canChangeContent(): void
    {
        $document = (new DocumentBuilder())->build();
        $now      = new DateTimeImmutable();

        $document->changeContent('New Content');

        self::assertSame('New Content', $document->getContent());
        self::assertGreaterThanOrEqual($now, $document->getUpdatedAt());

        $events = $document->flushEvents();
        self::assertCount(1, $events);
        self::assertInstanceOf(DocumentChangedContent::class, $events[0]);
        self::assertSame($document, $events[0]->document);
    }

    #[Test]
    public function noUpdateWhenMovingToSameDirectory(): void
    {
        $document          = (new DocumentBuilder())->build();
        $originalUpdatedAt = $document->getUpdatedAt();
        $originalDirectory = $document->getDirectory();

        $document->moveToDirectory(clone $originalDirectory);

        self::assertSame($originalDirectory->getId(), $document->getDirectory()->getId());
        self::assertSame($originalUpdatedAt, $document->getUpdatedAt());

        $events = $document->flushEvents();
        self::assertCount(0, $events);
    }

    #[Test]
    public function noUpdateWhenRenamingToSameTitle(): void
    {
        $document          = (new DocumentBuilder())->build();
        $originalUpdatedAt = $document->getUpdatedAt();
        $originalTitle     = $document->getTitle();

        $document->rename($originalTitle);

        self::assertSame($originalTitle, $document->getTitle());
        self::assertSame($originalUpdatedAt, $document->getUpdatedAt());

        $events = $document->flushEvents();
        self::assertCount(0, $events);
    }

    #[Test]
    public function noUpdateWhenChangingToSameContent(): void
    {
        $document          = (new DocumentBuilder())->build();
        $originalUpdatedAt = $document->getUpdatedAt();
        $originalContent   = $document->getContent();

        $document->changeContent($originalContent);

        self::assertSame($originalContent, $document->getContent());
        self::assertSame($originalUpdatedAt, $document->getUpdatedAt());

        $events = $document->flushEvents();
        self::assertCount(0, $events);
    }
}
