<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Chat\Domain\Entity;

use ChronicleKeeper\Chat\Domain\Entity\Message;
use ChronicleKeeper\Chat\Domain\ValueObject\MessageContext;
use ChronicleKeeper\Chat\Domain\ValueObject\MessageDebug;
use ChronicleKeeper\Chat\Domain\ValueObject\Role;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(Message::class)]
#[Small]
class MessageTest extends TestCase
{
    #[Test]
    public function itCanBeConstructedWithAllParameters(): void
    {
        $context = new MessageContext();
        $debug   = new MessageDebug();
        $message = new Message('123', Role::USER, 'Hello world', $context, $debug);

        self::assertSame('123', $message->getId());
        self::assertSame(Role::USER, $message->getRole());
        self::assertSame('Hello world', $message->getContent());
        self::assertSame($context, $message->getContext());
        self::assertSame($debug, $message->getDebug());
    }

    #[Test]
    public function itCanBeConstructedWithDefaultContext(): void
    {
        $message = new Message('123', Role::USER, 'Hello world');

        self::assertSame('123', $message->getId());
        self::assertSame(Role::USER, $message->getRole());
        self::assertSame('Hello world', $message->getContent());
    }

    #[Test]
    public function itCanCreateSystemMessage(): void
    {
        $message = Message::forSystem('System message');

        self::assertNotEmpty($message->getId());
        self::assertSame(Role::SYSTEM, $message->getRole());
        self::assertSame('System message', $message->getContent());
    }

    #[Test]
    public function itCanCreateUserMessage(): void
    {
        $message = Message::forUser('User message');

        self::assertNotEmpty($message->getId());
        self::assertSame(Role::USER, $message->getRole());
        self::assertSame('User message', $message->getContent());
    }

    #[Test]
    public function itCanCreateAssistantMessage(): void
    {
        $context = new MessageContext();
        $debug   = new MessageDebug();
        $message = Message::forAssistant('Assistant message', $context, $debug);

        self::assertNotEmpty($message->getId());
        self::assertSame(Role::ASSISTANT, $message->getRole());
        self::assertSame('Assistant message', $message->getContent());
        self::assertSame($context, $message->getContext());
        self::assertSame($debug, $message->getDebug());
    }

    #[Test]
    public function itCanCheckIfMessageIsRole(): void
    {
        $message = new Message('123', Role::USER, 'Hello world');

        self::assertTrue($message->isRole(Role::USER));
        self::assertFalse($message->isRole(Role::SYSTEM));
        self::assertFalse($message->isRole(Role::ASSISTANT));
    }

    #[Test]
    public function itCanCheckIfMessageIsSystem(): void
    {
        $systemMessage = new Message('123', Role::SYSTEM, 'System message');
        $userMessage   = new Message('456', Role::USER, 'User message');

        self::assertTrue($systemMessage->isSystem());
        self::assertFalse($userMessage->isSystem());
    }

    #[Test]
    public function itCanCheckIfMessageIsUser(): void
    {
        $userMessage   = new Message('123', Role::USER, 'User message');
        $systemMessage = new Message('456', Role::SYSTEM, 'System message');

        self::assertTrue($userMessage->isUser());
        self::assertFalse($systemMessage->isUser());
    }

    #[Test]
    public function itCanCheckIfMessageIsAssistant(): void
    {
        $assistantMessage = new Message('123', Role::ASSISTANT, 'Assistant message');
        $userMessage      = new Message('456', Role::USER, 'User message');

        self::assertTrue($assistantMessage->isAssistant());
        self::assertFalse($userMessage->isAssistant());
    }

    #[Test]
    public function itGeneratesUniqueIdsForStaticMethods(): void
    {
        $message1 = Message::forSystem('System message');
        $message2 = Message::forSystem('System message');

        self::assertNotSame($message1->getId(), $message2->getId());
    }
}
