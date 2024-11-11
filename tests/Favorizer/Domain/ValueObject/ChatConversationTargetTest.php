<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Favorizer\Domain\ValueObject;

use ChronicleKeeper\Favorizer\Domain\ValueObject\ChatConversationTarget;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Webmozart\Assert\InvalidArgumentException;

#[CoversClass(ChatConversationTarget::class)]
#[Small]
class ChatConversationTargetTest extends TestCase
{
    #[Test]
    public function instantiateWithValidData(): void
    {
        $target = new ChatConversationTarget(
            'f3ce2cce-888d-4812-8470-72cdd96faf4c',
            'Chat Conversation',
        );

        self::assertSame('f3ce2cce-888d-4812-8470-72cdd96faf4c', $target->getId());
        self::assertSame('Chat Conversation', $target->getTitle());
    }

    #[Test]
    public function instantiateWithInvalidUuid(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Value "invalid" is not a valid UUID.');

        new ChatConversationTarget('invalid', 'Chat Conversation');
    }

    #[Test]
    public function instantiateWithEmptyTitle(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Expected a non-empty value. Got: ""');

        new ChatConversationTarget('f3ce2cce-888d-4812-8470-72cdd96faf4c', '');
    }

    #[Test]
    public function jsonSerialize(): void
    {
        $target = new ChatConversationTarget(
            'f3ce2cce-888d-4812-8470-72cdd96faf4c',
            'Chat Conversation',
        );

        self::assertSame(
            [
                'type' => 'ChatConversationTarget',
                'id' => 'f3ce2cce-888d-4812-8470-72cdd96faf4c',
                'title' => 'Chat Conversation',
            ],
            $target->jsonSerialize(),
        );
    }
}
