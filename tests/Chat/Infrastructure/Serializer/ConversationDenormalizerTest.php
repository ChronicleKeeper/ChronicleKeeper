<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Chat\Infrastructure\Serializer;

use ChronicleKeeper\Chat\Domain\Entity\Conversation;
use ChronicleKeeper\Chat\Domain\Entity\MessageBag;
use ChronicleKeeper\Chat\Domain\ValueObject\Settings;
use ChronicleKeeper\Chat\Infrastructure\Serializer\ConversationDenormalizer;
use ChronicleKeeper\Library\Domain\Entity\Directory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\Exception\InvalidArgumentException;
use Symfony\Component\Serializer\Serializer;

#[CoversClass(ConversationDenormalizer::class)]
#[Small]
class ConversationDenormalizerTest extends TestCase
{
    private MockObject&Serializer $serializer;
    private ConversationDenormalizer $normalizer;

    protected function setUp(): void
    {
        $this->serializer = $this->createMock(Serializer::class);
        $this->normalizer = new ConversationDenormalizer();
        $this->normalizer->setDenormalizer($this->serializer);
    }

    protected function tearDown(): void
    {
        unset($this->serializer, $this->normalizer);
    }

    #[Test]
    public function itSupportsTheCorrectTypesWithNullFormat(): void
    {
        self::assertSame([Conversation::class => true], $this->normalizer->getSupportedTypes(null));
    }

    #[Test]
    public function itSupportsTheCorrectTypesWithStringFormat(): void
    {
        self::assertSame([Conversation::class => true], $this->normalizer->getSupportedTypes('json'));
    }

    #[Test]
    public function itSupportsTheCorrectTypeOnRuntimeCheck(): void
    {
        self::assertTrue($this->normalizer->supportsDenormalization([], Conversation::class));
    }

    #[Test]
    public function itDoesNotSupportWrongTypes(): void
    {
        self::assertFalse($this->normalizer->supportsDenormalization([], 'string'));
        self::assertFalse($this->normalizer->supportsDenormalization([], 'SomeOtherClass'));
    }

    #[Test]
    public function itFailsOnNonArrayInput(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Expected data to be an array for denormalization.');

        $this->normalizer->denormalize('not an array', Conversation::class);
    }

    #[Test]
    public function itDenormalizesConversationCorrectly(): void
    {
        $data = [
            'id' => '123',
            'title' => 'Test Conversation',
            'directory' => ['id' => 'directory-123'],
            'settings' => ['setting' => 'value'],
            'messages' => [],
        ];

        $directory  = $this->createMock(Directory::class);
        $settings   = $this->createMock(Settings::class);
        $messageBag = $this->createMock(MessageBag::class);

        $this->serializer->expects(self::exactly(3))
            ->method('denormalize')
            ->willReturnCallback(static function ($data, $type, $format, $context) use ($directory, $settings, $messageBag) {
                if ($type === Directory::class) {
                    return $directory;
                }

                if ($type === Settings::class) {
                    return $settings;
                }

                if ($type === MessageBag::class) {
                    return $messageBag;
                }

                return null;
            });

        $result = $this->normalizer->denormalize($data, Conversation::class);

        self::assertSame('123', $result->getId());
        self::assertSame('Test Conversation', $result->getTitle());
        self::assertSame($directory, $result->getDirectory());
        self::assertSame($settings, $result->getSettings());
        self::assertSame($messageBag, $result->getMessages());
    }

    #[Test]
    public function itDenormalizesWithMissingOptionalFields(): void
    {
        $data = [
            'id' => '456',
            'directory' => ['id' => 'directory-456'],
        ];

        $directory  = $this->createMock(Directory::class);
        $settings   = $this->createMock(Settings::class);
        $messageBag = $this->createMock(MessageBag::class);

        $this->serializer->expects(self::exactly(3))
            ->method('denormalize')
            ->willReturnCallback(static function ($data, $type, $format, $context) use ($directory, $settings, $messageBag) {
                if ($type === Directory::class) {
                    return $directory;
                }

                if ($type === Settings::class) {
                    return $settings;
                }

                if ($type === MessageBag::class) {
                    return $messageBag;
                }

                return null;
            });

        $result = $this->normalizer->denormalize($data, Conversation::class);

        self::assertSame('456', $result->getId());
        self::assertSame('', $result->getTitle()); // Default empty title
        self::assertSame($directory, $result->getDirectory());
        self::assertSame($settings, $result->getSettings());
        self::assertSame($messageBag, $result->getMessages());
    }

    #[Test]
    public function itDenormalizesWithCustomFormat(): void
    {
        $data = [
            'id' => '789',
            'title' => 'JSON Conversation',
            'directory' => ['id' => 'directory-789'],
            'settings' => ['setting' => 'json-value'],
            'messages' => ['messages' => []],
        ];

        $directory  = $this->createMock(Directory::class);
        $settings   = $this->createMock(Settings::class);
        $messageBag = $this->createMock(MessageBag::class);

        $this->serializer->expects(self::exactly(3))
            ->method('denormalize')
            ->with(self::anything(), self::anything(), 'json', ['key' => 'value'])
            ->willReturnCallback(static function ($data, $type, $format, $context) use ($directory, $settings, $messageBag) {
                if ($type === Directory::class) {
                    return $directory;
                }

                if ($type === Settings::class) {
                    return $settings;
                }

                if ($type === MessageBag::class) {
                    return $messageBag;
                }

                return null;
            });

        $result = $this->normalizer->denormalize($data, Conversation::class, 'json', ['key' => 'value']);

        self::assertSame('789', $result->getId());
        self::assertSame('JSON Conversation', $result->getTitle());
        self::assertSame($directory, $result->getDirectory());
        self::assertSame($settings, $result->getSettings());
        self::assertSame($messageBag, $result->getMessages());
    }
}
