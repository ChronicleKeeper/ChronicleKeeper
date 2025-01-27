<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\World\Domain\ValueObject;

use ChronicleKeeper\Test\Chat\Domain\Entity\ConversationBuilder;
use ChronicleKeeper\Test\World\Domain\Entity\ItemBuilder;
use ChronicleKeeper\World\Domain\ValueObject\ConversationReference;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(ConversationReference::class)]
#[Small]
final class ConversationReferenceTest extends TestCase
{
    #[Test]
    public function itIsConstructable(): void
    {
        $item         = (new ItemBuilder())->build();
        $conversation = (new ConversationBuilder())->build();
        $reference    = new ConversationReference($item, $conversation);

        self::assertSame($item, $reference->item);
        self::assertSame($conversation, $reference->conversation);
    }

    #[Test]
    public function itReturnsCorrectType(): void
    {
        $item         = (new ItemBuilder())->build();
        $conversation = (new ConversationBuilder())->build();
        $reference    = new ConversationReference($item, $conversation);

        self::assertSame('conversation', $reference->getType());
    }

    #[Test]
    public function itReturnsCorrectIcon(): void
    {
        $item         = (new ItemBuilder())->build();
        $conversation = (new ConversationBuilder())->build();
        $reference    = new ConversationReference($item, $conversation);

        self::assertSame('tabler:message', $reference->getIcon());
    }

    #[Test]
    public function itReturnsCorrectMediaId(): void
    {
        $item         = (new ItemBuilder())->build();
        $conversation = (new ConversationBuilder())->withId('conversation-id')->build();
        $reference    = new ConversationReference($item, $conversation);

        self::assertSame($conversation->getId(), $reference->getMediaId());
    }

    #[Test]
    public function itReturnsCorrectMediaTitle(): void
    {
        $item         = (new ItemBuilder())->build();
        $conversation = (new ConversationBuilder())->build();
        $reference    = new ConversationReference($item, $conversation);

        self::assertSame($conversation->getTitle(), $reference->getMediaTitle());
    }

    #[Test]
    public function itReturnsCorrectMediaDisplayName(): void
    {
        $item         = (new ItemBuilder())->build();
        $conversation = (new ConversationBuilder())->build();
        $reference    = new ConversationReference($item, $conversation);

        self::assertSame('Hauptverzeichnis > ' . $conversation->getTitle(), $reference->getMediaDisplayName());
    }

    #[Test]
    public function itReturnsCorrectGenericLinkIdentifier(): void
    {
        $item         = (new ItemBuilder())->build();
        $conversation = (new ConversationBuilder())->build();
        $reference    = new ConversationReference($item, $conversation);

        self::assertSame('conversation_' . $conversation->getId(), $reference->getGenericLinkIdentifier());
    }
}
