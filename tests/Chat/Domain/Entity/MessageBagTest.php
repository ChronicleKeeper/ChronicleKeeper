<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Chat\Domain\Entity;

use ChronicleKeeper\Chat\Domain\Entity\Message;
use ChronicleKeeper\Chat\Domain\Entity\MessageBag;
use ChronicleKeeper\Chat\Domain\ValueObject\Role;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(MessageBag::class)]
#[Small]
class MessageBagTest extends TestCase
{
    #[Test]
    public function itCanBeConstructedEmpty(): void
    {
        $messageBag = new MessageBag();

        self::assertCount(0, $messageBag);
    }

    #[Test]
    public function itCanBeConstructedWithMessages(): void
    {
        $message1   = new Message('1', Role::USER, 'Hello');
        $message2   = new Message('2', Role::ASSISTANT, 'Hi there');
        $messageBag = new MessageBag($message1, $message2);

        self::assertCount(2, $messageBag);
        self::assertSame($message1, $messageBag[0]);
        self::assertSame($message2, $messageBag[1]);
    }

    #[Test]
    public function itCanAddMessages(): void
    {
        $message1   = new Message('1', Role::USER, 'Hello');
        $message2   = new Message('2', Role::ASSISTANT, 'Hi there');
        $messageBag = new MessageBag($message1);

        $messageBag[] = $message2;

        self::assertCount(2, $messageBag);
        self::assertSame($message1, $messageBag[0]);
        self::assertSame($message2, $messageBag[1]);
    }

    #[Test]
    public function itCanBeIterated(): void
    {
        $message1   = new Message('1', Role::USER, 'Hello');
        $message2   = new Message('2', Role::ASSISTANT, 'Hi there');
        $messageBag = new MessageBag($message1, $message2);

        $iteratedMessages = [];
        foreach ($messageBag as $message) {
            $iteratedMessages[] = $message;
        }

        self::assertCount(2, $iteratedMessages);
        self::assertSame($message1, $iteratedMessages[0]);
        self::assertSame($message2, $iteratedMessages[1]);
    }

    #[Test]
    public function itCanBeReset(): void
    {
        $message1   = new Message('1', Role::USER, 'Hello');
        $message2   = new Message('2', Role::ASSISTANT, 'Hi there');
        $messageBag = new MessageBag($message1, $message2);

        self::assertCount(2, $messageBag);

        $messageBag->reset();

        self::assertCount(0, $messageBag);
    }

    #[Test]
    public function itCanReturnArrayCopy(): void
    {
        $message1   = new Message('1', Role::USER, 'Hello');
        $message2   = new Message('2', Role::ASSISTANT, 'Hi there');
        $messageBag = new MessageBag($message1, $message2);

        $arrayCopy = $messageBag->getArrayCopy();

        self::assertCount(2, $arrayCopy);
        self::assertSame($message1, $arrayCopy[0]);
        self::assertSame($message2, $arrayCopy[1]);
    }

    #[Test]
    public function itHandlesVariadicParameters(): void
    {
        $messages = [
            new Message('1', Role::SYSTEM, 'System'),
            new Message('2', Role::USER, 'User'),
            new Message('3', Role::ASSISTANT, 'Assistant'),
        ];

        $messageBag = new MessageBag(...$messages);

        self::assertCount(3, $messageBag);
        foreach ($messages as $index => $message) {
            self::assertSame($message, $messageBag[$index]);
        }
    }
}
