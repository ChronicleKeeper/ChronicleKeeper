<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Chat\Infrastructure\Serializer;

use ChronicleKeeper\Chat\Infrastructure\LLMChain\ExtendedMessage;
use ChronicleKeeper\Chat\Infrastructure\LLMChain\ExtendedMessageBag;
use ChronicleKeeper\Chat\Infrastructure\Serializer\ExtendedMessageBagDenormalizer;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use stdClass;
use Symfony\Component\Serializer\Serializer;
use Webmozart\Assert\InvalidArgumentException;

#[CoversClass(ExtendedMessageBagDenormalizer::class)]
#[UsesClass(ExtendedMessage::class)]
#[Small]
class ExtendedMessageBagDenormalizerTest extends TestCase
{
    private MockObject&Serializer $serializer;
    private ExtendedMessageBagDenormalizer $normalizer;

    protected function setUp(): void
    {
        $this->serializer = $this->createMock(Serializer::class);

        $this->normalizer = new ExtendedMessageBagDenormalizer();
        $this->normalizer->setDenormalizer($this->serializer);
    }

    protected function tearDown(): void
    {
        unset($this->serializer, $this->normalizer);
    }

    public function testThatItSupportsTheCorrectTypesWithNullFormat(): void
    {
        self::assertSame([ExtendedMessageBag::class => true], $this->normalizer->getSupportedTypes(null));
    }

    public function testThatItSupportsTheCorrectTypesWithStringFormat(): void
    {
        self::assertSame([ExtendedMessageBag::class => true], $this->normalizer->getSupportedTypes('json'));
    }

    public function testThatItSupportsTheCorrectTypeOnRuntimeCheck(): void
    {
        self::assertTrue($this->normalizer->supportsDenormalization([], ExtendedMessageBag::class));
        self::assertFalse($this->normalizer->supportsDenormalization([], stdClass::class));
    }

    public function testThatDenormalizationFailsOnNonArrayInput(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->normalizer->denormalize('', ExtendedMessageBag::class);
    }

    public function testThatTheNormalizerIsExecutingTheNormalizer(): void
    {
        $this->serializer->expects($this->once())
            ->method('denormalize')
            ->with(
                self::callback(static function (mixed $data): true {
                    self::assertIsArray($data);
                    self::assertSame(['foo'], $data);

                    return true;
                }),
                self::callback(static function (string $type): true {
                    self::assertSame(ExtendedMessage::class . '[]', $type);

                    return true;
                }),
            )
            ->willReturn([]);

        $obj = $this->normalizer->denormalize(['foo'], ExtendedMessageBag::class);

        self::assertInstanceOf(ExtendedMessageBag::class, $obj);
    }
}
