<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Library\Domain\ValueObject\DirectoryContent;

use ChronicleKeeper\Library\Domain\ValueObject\DirectoryContent\Element;
use ChronicleKeeper\Test\Chat\Domain\Entity\ConversationBuilder;
use ChronicleKeeper\Test\Chat\Domain\Entity\MessageBagBuilder;
use ChronicleKeeper\Test\Chat\Domain\Entity\MessageBuilder;
use ChronicleKeeper\Test\Document\Domain\Entity\DocumentBuilder;
use ChronicleKeeper\Test\Image\Domain\Entity\ImageBuilder;
use DateTimeImmutable;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(Element::class)]
#[Small]
class ElementTest extends TestCase
{
    #[Test]
    public function itCanBeConstructedWithMinimalArguments(): void
    {
        $element = new Element('id', 'type', 'title', 'slug', 100);

        self::assertSame('id', $element->id);
        self::assertSame('type', $element->type);
        self::assertSame('title', $element->title);
        self::assertSame('slug', $element->slug);
        self::assertSame(100, $element->size);
        self::assertInstanceOf(DateTimeImmutable::class, $element->updatedAt);
    }

    #[Test]
    public function itCanBeConstructedWithAllArguments(): void
    {
        $updatedAt = new DateTimeImmutable();
        $element   = new Element('id', 'type', 'title', 'slug', 100, $updatedAt);

        self::assertSame('id', $element->id);
        self::assertSame('type', $element->type);
        self::assertSame('title', $element->title);
        self::assertSame('slug', $element->slug);
        self::assertSame(100, $element->size);
        self::assertSame($updatedAt, $element->updatedAt);
    }

    #[Test]
    public function itCanBeCreatedFromDocumentEntity(): void
    {
        $document = (new DocumentBuilder())
            ->withId('23225cbe-9425-4832-ac3c-132397c8986f')
            ->withTitle('title')
            ->build();
        $element  = Element::fromDocumentEntity($document);

        self::assertSame('23225cbe-9425-4832-ac3c-132397c8986f', $element->id);
        self::assertSame('document', $element->type);
        self::assertSame('title', $element->title);
        self::assertSame('title', $element->slug);
        self::assertSame(15, $element->size);
        self::assertInstanceOf(DateTimeImmutable::class, $element->updatedAt);
    }

    #[Test]
    public function itCanBeCreatedFromImageEntity(): void
    {
        $image = (new ImageBuilder())
            ->withId('3ee6ad3f-1a32-43b4-bcb0-175a4ad98df7')
            ->withTitle('title')
            ->build();

        $element = Element::fromImageEntity($image);

        self::assertSame('3ee6ad3f-1a32-43b4-bcb0-175a4ad98df7', $element->id);
        self::assertSame('image', $element->type);
        self::assertSame('title', $element->title);
        self::assertSame('title', $element->slug);
        self::assertSame(21, $element->size);
        self::assertInstanceOf(DateTimeImmutable::class, $element->updatedAt);
    }

    #[Test]
    public function itCanBeCreatedFromConversationEntity(): void
    {
        $conversation = (new ConversationBuilder())
            ->withTitle('title')
            ->withId('5244cbff-a465-4a90-887a-a9df56bcf513')
            ->withMessages((new MessageBagBuilder())->withMessage(
                (new MessageBuilder())->asUser()->withContent('Test message')->build(),
            )->build())
            ->build();

        $element = Element::fromConversationEntity($conversation);

        self::assertSame('5244cbff-a465-4a90-887a-a9df56bcf513', $element->id);
        self::assertSame('conversation', $element->type);
        self::assertSame('title', $element->title);
        self::assertSame('title', $element->slug);
        self::assertSame(1, $element->size);
        self::assertNotNull($element->updatedAt);
    }
}
