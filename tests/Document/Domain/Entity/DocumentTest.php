<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Document\Domain\Entity;

use ChronicleKeeper\Document\Domain\Entity\Document;
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
    public function isConstructable(): void
    {
        $document = new Document('foo', 'bar');

        self::assertSame('foo', $document->title);
        self::assertSame('bar', $document->content);
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
    public function theSizeIsCorrectlyCalculable(): void
    {
        $document = (new DocumentBuilder())->withContent('foo bar baz')->build();

        self::assertSame(11, $document->getSize());
    }

    #[Test]
    public function theContentHasIsCorrectlyCalculated(): void
    {
        $document = (new DocumentBuilder())->withContent('foo bar baz')->build();

        self::assertSame('c7567e8b39e2428e38bf9c9226ac68de4c67dc39', $document->getContentHash());
    }
}
