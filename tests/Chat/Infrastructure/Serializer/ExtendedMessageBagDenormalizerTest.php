<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Chat\Infrastructure\Serializer;

use ChronicleKeeper\Chat\Domain\Entity\ExtendedMessage;
use ChronicleKeeper\Chat\Domain\Entity\ExtendedMessageBag;
use ChronicleKeeper\Chat\Infrastructure\Serializer\ExtendedMessageBagDenormalizer;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use stdClass;
use Symfony\Component\Serializer\Serializer;
use Webmozart\Assert\InvalidArgumentException;

#[CoversClass(ExtendedMessageBagDenormalizer::class)]
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

    #[Test]
    public function itSupportsTheCorrectTypesWithNullFormat(): void
    {
        self::assertSame([ExtendedMessageBag::class => true], $this->normalizer->getSupportedTypes(null));
    }

    #[Test]
    public function itSupportsTheCorrectTypesWithStringFormat(): void
    {
        self::assertSame([ExtendedMessageBag::class => true], $this->normalizer->getSupportedTypes('json'));
    }

    #[Test]
    public function itSupportsTheCorrectTypeOnRuntimeCheck(): void
    {
        self::assertTrue($this->normalizer->supportsDenormalization([], ExtendedMessageBag::class));
        self::assertFalse($this->normalizer->supportsDenormalization([], stdClass::class));
    }

    #[Test]
    public function theDenormalizationFailsOnNonArrayInput(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->normalizer->denormalize('', ExtendedMessageBag::class);
    }

    #[Test]
    public function theNormalizerIsExecutingTheNormalizer(): void
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
