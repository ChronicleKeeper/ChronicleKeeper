<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Chat\Infrastructure\Serializer;

use ChronicleKeeper\Chat\Domain\Entity\Message;
use ChronicleKeeper\Chat\Domain\Entity\MessageBag;
use ChronicleKeeper\Chat\Domain\ValueObject\Role;
use ChronicleKeeper\Chat\Infrastructure\Serializer\MessageBagNormalizer;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\Exception\InvalidArgumentException;
use Symfony\Component\Serializer\Serializer;

#[CoversClass(MessageBagNormalizer::class)]
#[Small]
class MessageBagNormalizerTest extends TestCase
{
    private MockObject&Serializer $serializer;
    private MessageBagNormalizer $normalizer;

    protected function setUp(): void
    {
        $this->serializer = $this->createMock(Serializer::class);
        $this->normalizer = new MessageBagNormalizer();
        $this->normalizer->setNormalizer($this->serializer);
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
        $messageBag = new MessageBag();
        self::assertTrue($this->normalizer->supportsNormalization($messageBag));
    }

    #[Test]
    public function itDoesNotSupportWrongTypes(): void
    {
        self::assertFalse($this->normalizer->supportsNormalization('string'));
        self::assertFalse($this->normalizer->supportsNormalization(123));
        self::assertFalse($this->normalizer->supportsNormalization([]));
    }

    #[Test]
    public function itFailsOnNonMessageBagInput(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Expected Instance of "' . MessageBag::class . '"');

        $this->normalizer->normalize('not a message bag');
    }

    #[Test]
    public function itNormalizesEmptyMessageBag(): void
    {
        $messageBag = new MessageBag();

        $result = $this->normalizer->normalize($messageBag);

        self::assertCount(0, $result);
    }

    #[Test]
    public function itNormalizesMessageBagWithSingleMessage(): void
    {
        $message    = new Message('123', Role::USER, 'Hello');
        $messageBag = new MessageBag($message);

        $this->serializer->expects(self::once())
            ->method('normalize')
            ->with($message, null, [])
            ->willReturn(['id' => '123', 'role' => 'user', 'content' => 'Hello']);

        $result = $this->normalizer->normalize($messageBag);

        self::assertCount(1, $result);
        self::assertSame(['id' => '123', 'role' => 'user', 'content' => 'Hello'], $result[0]);
    }

    #[Test]
    public function itNormalizesMessageBagWithMultipleMessages(): void
    {
        $message1   = new Message('123', Role::USER, 'Hello');
        $message2   = new Message('456', Role::ASSISTANT, 'Hi there');
        $messageBag = new MessageBag($message1, $message2);

        $this->serializer->expects(self::exactly(2))
            ->method('normalize')
            ->willReturnCallback(static function ($message, $format, $context) {
                if ($message instanceof Message && $message->getId() === '123') {
                    return [
                        'id' => '123',
                        'role' => 'user',
                        'content' => 'Hello',
                    ];
                }

                if ($message instanceof Message && $message->getId() === '456') {
                    return [
                        'id' => '456',
                        'role' => 'assistant',
                        'content' => 'Hi there',
                    ];
                }

                return [];
            });

        $result = $this->normalizer->normalize($messageBag);

        self::assertCount(2, $result);
        self::assertSame(['id' => '123', 'role' => 'user', 'content' => 'Hello'], $result[0]);
        self::assertSame(['id' => '456', 'role' => 'assistant', 'content' => 'Hi there'], $result[1]);
    }

    #[Test]
    public function itNormalizesWithCustomFormat(): void
    {
        $message    = new Message('789', Role::SYSTEM, 'System message');
        $messageBag = new MessageBag($message);

        $this->serializer->expects(self::once())
            ->method('normalize')
            ->with($message, 'json', ['key' => 'value'])
            ->willReturn(['id' => '789', 'role' => 'system', 'content' => 'System message']);

        $result = $this->normalizer->normalize($messageBag, 'json', ['key' => 'value']);

        self::assertCount(1, $result);
        self::assertSame(['id' => '789', 'role' => 'system', 'content' => 'System message'], $result[0]);
    }
}
