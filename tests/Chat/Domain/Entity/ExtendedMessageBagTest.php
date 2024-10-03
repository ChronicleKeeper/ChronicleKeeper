<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Chat\Domain\Entity;

use ChronicleKeeper\Chat\Domain\Entity\ExtendedMessage;
use ChronicleKeeper\Chat\Domain\Entity\ExtendedMessageBag;
use PhpLlm\LlmChain\Message\MessageInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(ExtendedMessageBag::class)]
#[Small]
class ExtendedMessageBagTest extends TestCase
{
    #[Test]
    public function constructsExtendedMessageBagCorrectly(): void
    {
        $message1 = $this->createMock(MessageInterface::class);
        $message2 = $this->createMock(MessageInterface::class);

        $extendedMessage1 = new ExtendedMessage($message1);
        $extendedMessage2 = new ExtendedMessage($message2);

        $extendedMessageBag = new ExtendedMessageBag($extendedMessage1, $extendedMessage2);

        self::assertCount(2, $extendedMessageBag);
        self::assertSame($extendedMessage1, $extendedMessageBag[0]);
        self::assertSame($extendedMessage2, $extendedMessageBag[1]);
    }

    #[Test]
    public function convertsToLLMChainMessagesCorrectly(): void
    {
        $message1 = $this->createMock(MessageInterface::class);
        $message2 = $this->createMock(MessageInterface::class);

        $extendedMessage1 = new ExtendedMessage($message1);
        $extendedMessage2 = new ExtendedMessage($message2);

        $extendedMessageBag = new ExtendedMessageBag($extendedMessage1, $extendedMessage2);
        $llmChainMessages   = $extendedMessageBag->getLLMChainMessages();

        self::assertCount(2, $llmChainMessages);
        self::assertSame($message1, $llmChainMessages[0]);
        self::assertSame($message2, $llmChainMessages[1]);
    }

    #[Test]
    public function serializesToJsonCorrectly(): void
    {
        $message1 = $this->createMock(MessageInterface::class);
        $message2 = $this->createMock(MessageInterface::class);

        $extendedMessage1 = new ExtendedMessage($message1);
        $extendedMessage2 = new ExtendedMessage($message2);

        $extendedMessageBag = new ExtendedMessageBag($extendedMessage1, $extendedMessage2);
        $serialized         = $extendedMessageBag->jsonSerialize();

        self::assertIsArray($serialized);
        self::assertCount(2, $serialized);
        self::assertSame($extendedMessage1, $serialized[0]);
        self::assertSame($extendedMessage2, $serialized[1]);
    }
}
