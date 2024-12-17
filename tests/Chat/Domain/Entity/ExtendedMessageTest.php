<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Chat\Domain\Entity;

use ChronicleKeeper\Chat\Domain\Entity\ExtendedMessage;
use PhpLlm\LlmChain\Model\Message\MessageInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Uuid;

#[CoversClass(ExtendedMessage::class)]
#[Small]
class ExtendedMessageTest extends TestCase
{
    #[Test]
    public function constructsExtendedMessageCorrectly(): void
    {
        $message = $this->createMock(MessageInterface::class);

        $extendedMessage = new ExtendedMessage($message);

        self::assertTrue(Uuid::isValid($extendedMessage->id));
        self::assertSame($message, $extendedMessage->message);
    }

    #[Test]
    public function serializesToJsonCorrectly(): void
    {
        $message = $this->createMock(MessageInterface::class);

        $extendedMessage = new ExtendedMessage($message);
        $serialized      = $extendedMessage->jsonSerialize();

        self::assertSame($extendedMessage->id, $serialized['id']);
        self::assertSame($message, $serialized['message']);
    }
}
