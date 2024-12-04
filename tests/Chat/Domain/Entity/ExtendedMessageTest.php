<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Chat\Domain\Entity;

use ChronicleKeeper\Chat\Domain\Entity\ExtendedMessage;
use ChronicleKeeper\Document\Domain\Entity\Document;
use ChronicleKeeper\Library\Domain\Entity\Image;
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
        $message     = $this->createMock(MessageInterface::class);
        $documents   = [$this->createMock(Document::class)];
        $images      = [$this->createMock(Image::class)];
        $calledTools = [['tool' => 'exampleTool', 'arguments' => ['arg1' => 'value1']]];

        $extendedMessage = new ExtendedMessage($message, $documents, $images, $calledTools);

        self::assertTrue(Uuid::isValid($extendedMessage->id));
        self::assertSame($message, $extendedMessage->message);
        self::assertSame($documents, $extendedMessage->documents);
        self::assertSame($images, $extendedMessage->images);
        self::assertSame($calledTools, $extendedMessage->calledTools);
    }

    #[Test]
    public function serializesToJsonCorrectly(): void
    {
        $message     = $this->createMock(MessageInterface::class);
        $documents   = [$this->createMock(Document::class)];
        $images      = [$this->createMock(Image::class)];
        $calledTools = [['tool' => 'exampleTool', 'arguments' => ['arg1' => 'value1']]];

        $extendedMessage = new ExtendedMessage($message, $documents, $images, $calledTools);
        $serialized      = $extendedMessage->jsonSerialize();

        self::assertSame($extendedMessage->id, $serialized['id']);
        self::assertSame($message, $serialized['message']);
        self::assertSame($documents, $serialized['documents']);
        self::assertSame($images, $serialized['images']);
        self::assertSame($calledTools, $serialized['calledTools']);
    }
}
