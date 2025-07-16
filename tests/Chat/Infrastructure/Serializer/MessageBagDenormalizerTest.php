<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Chat\Infrastructure\Serializer;

use ChronicleKeeper\Chat\Domain\Entity\Message;
use ChronicleKeeper\Chat\Domain\Entity\MessageBag;
use ChronicleKeeper\Chat\Domain\ValueObject\Role;
use ChronicleKeeper\Chat\Infrastructure\Serializer\MessageBagDenormalizer;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\Exception\InvalidArgumentException;
use Symfony\Component\Serializer\Serializer;

#[CoversClass(MessageBagDenormalizer::class)]
#[Small]
class MessageBagDenormalizerTest extends TestCase
{
    private MockObject&Serializer $serializer;
    private MessageBagDenormalizer $normalizer;

    protected function setUp(): void
    {
        $this->serializer = $this->createMock(Serializer::class);
        $this->normalizer = new MessageBagDenormalizer();
        $this->normalizer->setDenormalizer($this->serializer);
    }

    protected function tearDown(): void
    {
        unset($this->serializer, $this->normalizer);
    }

    #[Test]
    public function itSupportsTheCorrectTypesWithNullFormat(): void
    {
        self::assertSame([MessageBag::class => true], $this->normalizer->getSupportedTypes(null));
    }

    #[Test]
    public function itSupportsTheCorrectTypesWithStringFormat(): void
    {
        self::assertSame([MessageBag::class => true], $this->normalizer->getSupportedTypes('json'));
    }

    #[Test]
    public function itSupportsTheCorrectTypeOnRuntimeCheck(): void
    {
        self::assertTrue($this->normalizer->supportsDenormalization([], MessageBag::class));
    }

    #[Test]
    public function itDoesNotSupportWrongTypes(): void
    {
        self::assertFalse($this->normalizer->supportsDenormalization([], 'string'));
        self::assertFalse($this->normalizer->supportsDenormalization([], Message::class));
    }

    #[Test]
    public function itFailsOnNonArrayInput(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Expected data to be an array for denormalization.');

        $this->normalizer->denormalize('not an array', MessageBag::class);
    }

    #[Test]
    public function itDenormalizesEmptyArray(): void
    {
        $result = $this->normalizer->denormalize([], MessageBag::class);

        self::assertCount(0, $result);
    }

    #[Test]
    public function itDenormalizesArrayWithSingleMessage(): void
    {
        $messageData = [
            ['id' => '123', 'role' => 'user', 'content' => 'Hello'],
        ];

        $message = new Message('123', Role::USER, 'Hello');
        $this->serializer->expects(self::once())
            ->method('denormalize')
            ->with($messageData[0], Message::class, null, [])
            ->willReturn($message);

        $result = $this->normalizer->denormalize($messageData, MessageBag::class);

        self::assertCount(1, $result);
        self::assertSame($message, $result[0]);
    }

    #[Test]
    public function itDenormalizesArrayWithMultipleMessages(): void
    {
        $messageData = [
            ['id' => '123', 'role' => 'user', 'content' => 'Hello'],
            ['id' => '456', 'role' => 'assistant', 'content' => 'Hi there'],
        ];

        $message1 = new Message('123', Role::USER, 'Hello');
        $message2 = new Message('456', Role::ASSISTANT, 'Hi there');

        $this->serializer->expects(self::exactly(2))
            ->method('denormalize')
            ->willReturnCallback(static function ($data, $type, $format, $context) use ($message1, $message2) {
                if ($data['id'] === '123') {
                    return $message1;
                }

                if ($data['id'] === '456') {
                    return $message2;
                }

                return null;
            });

        $result = $this->normalizer->denormalize($messageData, MessageBag::class);

        self::assertCount(2, $result);
        self::assertSame($message1, $result[0]);
        self::assertSame($message2, $result[1]);
    }

    #[Test]
    public function itDenormalizesWithCustomFormat(): void
    {
        $messageData = [
            ['id' => '789', 'role' => 'system', 'content' => 'System message'],
        ];

        $message = new Message('789', Role::SYSTEM, 'System message');
        $this->serializer->expects(self::once())
            ->method('denormalize')
            ->with($messageData[0], Message::class, 'json', ['key' => 'value'])
            ->willReturn($message);

        $result = $this->normalizer->denormalize($messageData, MessageBag::class, 'json', ['key' => 'value']);

        self::assertCount(1, $result);
        self::assertSame($message, $result[0]);
    }
}
