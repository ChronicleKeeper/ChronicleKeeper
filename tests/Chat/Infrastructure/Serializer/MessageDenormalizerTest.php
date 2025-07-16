<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Chat\Infrastructure\Serializer;

use ChronicleKeeper\Chat\Domain\Entity\Message;
use ChronicleKeeper\Chat\Domain\ValueObject\MessageContext;
use ChronicleKeeper\Chat\Domain\ValueObject\Role;
use ChronicleKeeper\Chat\Infrastructure\Serializer\MessageDenormalizer;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\Exception\InvalidArgumentException;
use Symfony\Component\Serializer\Serializer;

#[CoversClass(MessageDenormalizer::class)]
#[Small]
class MessageDenormalizerTest extends TestCase
{
    private MockObject&Serializer $serializer;
    private MessageDenormalizer $normalizer;

    protected function setUp(): void
    {
        $this->serializer = $this->createMock(Serializer::class);
        $this->normalizer = new MessageDenormalizer();
        $this->normalizer->setDenormalizer($this->serializer);
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
        self::assertTrue($this->normalizer->supportsDenormalization([], Message::class));
    }

    #[Test]
    public function itFailsOnNonArrayInput(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Expected data to be an array for denormalization.');

        $this->normalizer->denormalize('', Message::class);
    }

    #[Test]
    public function theNormalizationWorksAsExpected(): void
    {
        $this->serializer->expects(self::once())
            ->method('denormalize')
            ->with([], MessageContext::class)
            ->willReturn(new MessageContext());

        $data = [
            'id' => '12345',
            'role' => 'user',
            'content' => 'Hello world',
            'context' => [],
        ];

        $obj = $this->normalizer->denormalize($data, Message::class);

        self::assertSame('12345', $obj->getId());
        self::assertSame(Role::USER, $obj->getRole());
        self::assertSame('Hello world', $obj->getContent());
    }
}
