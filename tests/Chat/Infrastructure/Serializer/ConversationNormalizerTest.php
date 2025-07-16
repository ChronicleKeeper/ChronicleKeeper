<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Chat\Infrastructure\Serializer;

use ChronicleKeeper\Chat\Domain\Entity\Conversation;
use ChronicleKeeper\Chat\Domain\Entity\MessageBag;
use ChronicleKeeper\Chat\Domain\ValueObject\Settings;
use ChronicleKeeper\Chat\Infrastructure\Serializer\ConversationNormalizer;
use ChronicleKeeper\Library\Domain\Entity\Directory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\Exception\InvalidArgumentException;
use Symfony\Component\Serializer\Serializer;

#[CoversClass(ConversationNormalizer::class)]
#[Small]
class ConversationNormalizerTest extends TestCase
{
    private MockObject&Serializer $serializer;
    private ConversationNormalizer $normalizer;

    protected function setUp(): void
    {
        $this->serializer = $this->createMock(Serializer::class);
        $this->normalizer = new ConversationNormalizer();
        $this->normalizer->setNormalizer($this->serializer);
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
        $directory    = $this->createMock(Directory::class);
        $settings     = $this->createMock(Settings::class);
        $messageBag   = $this->createMock(MessageBag::class);
        $conversation = new Conversation('123', 'Test', $directory, $settings, $messageBag);

        self::assertTrue($this->normalizer->supportsNormalization($conversation));
    }

    #[Test]
    public function itDoesNotSupportWrongTypes(): void
    {
        self::assertFalse($this->normalizer->supportsNormalization('string'));
        self::assertFalse($this->normalizer->supportsNormalization(123));
        self::assertFalse($this->normalizer->supportsNormalization([]));
    }

    #[Test]
    public function itFailsOnNonConversationInput(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Expected Instance of "' . Conversation::class . '"');

        $this->normalizer->normalize('not a conversation');
    }

    #[Test]
    public function itNormalizesConversationCorrectly(): void
    {
        $directory    = $this->createMock(Directory::class);
        $settings     = $this->createMock(Settings::class);
        $messageBag   = $this->createMock(MessageBag::class);
        $conversation = new Conversation('123', 'Test Conversation', $directory, $settings, $messageBag);

        $this->serializer->expects(self::exactly(3))
            ->method('normalize')
            ->willReturnCallback(static function ($data, $format, $context) {
                if ($data instanceof Directory) {
                    return ['id' => 'directory-123'];
                }

                if ($data instanceof Settings) {
                    return ['setting' => 'value'];
                }

                if ($data instanceof MessageBag) {
                    return [];
                }

                return null;
            });

        $result = $this->normalizer->normalize($conversation);

        self::assertSame('123', $result['id']);
        self::assertSame('Test Conversation', $result['title']);
        self::assertSame(['id' => 'directory-123'], $result['directory']);
        self::assertSame(['setting' => 'value'], $result['settings']);
        self::assertSame([], $result['messages']);
    }

    #[Test]
    public function itNormalizesWithCustomFormat(): void
    {
        $directory    = $this->createMock(Directory::class);
        $settings     = $this->createMock(Settings::class);
        $messageBag   = $this->createMock(MessageBag::class);
        $conversation = new Conversation('456', 'Another Conversation', $directory, $settings, $messageBag);

        $this->serializer->expects(self::exactly(3))
            ->method('normalize')
            ->with(self::anything(), 'json', ['key' => 'value'])
            ->willReturnCallback(static function ($data, $format, $context) {
                if ($data instanceof Directory) {
                    return ['id' => 'directory-456'];
                }

                if ($data instanceof Settings) {
                    return ['setting' => 'json-value'];
                }

                if ($data instanceof MessageBag) {
                    return ['messages' => []];
                }

                return null;
            });

        $result = $this->normalizer->normalize($conversation, 'json', ['key' => 'value']);

        self::assertSame('456', $result['id']);
        self::assertSame('Another Conversation', $result['title']);
        self::assertSame(['id' => 'directory-456'], $result['directory']);
        self::assertSame(['setting' => 'json-value'], $result['settings']);
        self::assertSame(['messages' => []], $result['messages']);
    }
}
