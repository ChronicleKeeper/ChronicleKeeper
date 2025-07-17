<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Chat\Infrastructure\Serializer;

use ChronicleKeeper\Chat\Domain\Entity\Message;
use ChronicleKeeper\Chat\Domain\ValueObject\MessageContext;
use ChronicleKeeper\Chat\Domain\ValueObject\Role;
use ChronicleKeeper\Chat\Infrastructure\Serializer\MessageNormalizer;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\Exception\InvalidArgumentException;
use Symfony\Component\Serializer\Serializer;

#[CoversClass(MessageNormalizer::class)]
#[Small]
class MessageNormalizerTest extends TestCase
{
    private MockObject&Serializer $serializer;
    private MessageNormalizer $normalizer;

    protected function setUp(): void
    {
        $this->serializer = $this->createMock(Serializer::class);
        $this->normalizer = new MessageNormalizer();
        $this->normalizer->setNormalizer($this->serializer);
    }

    protected function tearDown(): void
    {
        unset($this->serializer, $this->normalizer);
    }

    #[Test]
    public function itSupportsTheCorrectTypesWithNullFormat(): void
    {
        self::assertSame([Message::class => true], $this->normalizer->getSupportedTypes(null));
    }

    #[Test]
    public function itSupportsTheCorrectTypesWithStringFormat(): void
    {
        self::assertSame([Message::class => true], $this->normalizer->getSupportedTypes('json'));
    }

    #[Test]
    public function itSupportsTheCorrectTypeOnRuntimeCheck(): void
    {
        $message = new Message('123', Role::USER, 'Hello');
        self::assertTrue($this->normalizer->supportsNormalization($message));
    }

    #[Test]
    public function itDoesNotSupportWrongTypes(): void
    {
        self::assertFalse($this->normalizer->supportsNormalization('string'));
        self::assertFalse($this->normalizer->supportsNormalization(123));
        self::assertFalse($this->normalizer->supportsNormalization([]));
    }

    #[Test]
    public function itFailsOnNonMessageInput(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Expected Instance of "' . Message::class . '"');

        $this->normalizer->normalize('not a message');
    }

    #[Test]
    public function itNormalizesMessageCorrectly(): void
    {
        $context = new MessageContext();
        $message = new Message('123', Role::USER, 'Hello world', $context);

        $this->serializer->expects(self::once())
            ->method('normalize')
            ->with($context, null, [])
            ->willReturn(['documents' => [], 'images' => []]);

        $result = $this->normalizer->normalize($message);

        self::assertSame('123', $result['id']);
        self::assertSame('user', $result['role']);
        self::assertSame('Hello world', $result['content']);
        self::assertSame(['documents' => [], 'images' => []], $result['context']);
    }

    #[Test]
    public function itNormalizesWithCustomFormat(): void
    {
        $context = new MessageContext();
        $message = new Message('456', Role::ASSISTANT, 'Assistant response', $context);

        $this->serializer->expects(self::once())
            ->method('normalize')
            ->with($context, 'json', ['key' => 'value'])
            ->willReturn(['documents' => [], 'images' => []]);

        $result = $this->normalizer->normalize($message, 'json', ['key' => 'value']);

        self::assertSame('456', $result['id']);
        self::assertSame('assistant', $result['role']);
        self::assertSame('Assistant response', $result['content']);
        self::assertSame(['documents' => [], 'images' => []], $result['context']);
    }

    #[Test]
    public function itNormalizesSystemMessage(): void
    {
        $context = new MessageContext();
        $message = new Message('789', Role::SYSTEM, 'System message', $context);

        $this->serializer->expects(self::once())
            ->method('normalize')
            ->with($context, null, [])
            ->willReturn(['documents' => [], 'images' => []]);

        $result = $this->normalizer->normalize($message);

        self::assertSame('789', $result['id']);
        self::assertSame('system', $result['role']);
        self::assertSame('System message', $result['content']);
        self::assertSame(['documents' => [], 'images' => []], $result['context']);
    }
}
