<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Shared\Infrastructure\LLMChain\Serializer;

use ChronicleKeeper\Shared\Infrastructure\LLMChain\Serializer\LLMUserMessageDenormalizer;
use PhpLlm\LlmChain\Model\Message\AssistantMessage;
use PhpLlm\LlmChain\Model\Message\Content\Image;
use PhpLlm\LlmChain\Model\Message\Content\Text;
use PhpLlm\LlmChain\Model\Message\MessageInterface;
use PhpLlm\LlmChain\Model\Message\SystemMessage;
use PhpLlm\LlmChain\Model\Message\ToolCallMessage;
use PhpLlm\LlmChain\Model\Message\UserMessage;
use PhpLlm\LlmChain\Model\Response\ToolCall;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use stdClass;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Webmozart\Assert\InvalidArgumentException;

#[CoversClass(LLMUserMessageDenormalizer::class)]
#[Small]
class LLMUserMessageDenormalizerTest extends TestCase
{
    private MockObject&DenormalizerInterface $denormalizer;
    private LLMUserMessageDenormalizer $normalizer;

    protected function setUp(): void
    {
        $this->denormalizer = $this->createMock(DenormalizerInterface::class);

        $this->normalizer = new LLMUserMessageDenormalizer();
        $this->normalizer->setDenormalizer($this->denormalizer);
    }

    protected function tearDown(): void
    {
        unset($this->denormalizer, $this->normalizer);
    }

    #[Test]
    public function itSupportsTheCorrectTypesWithNullFormat(): void
    {
        self::assertSame([MessageInterface::class => true], $this->normalizer->getSupportedTypes(null));
    }

    #[Test]
    public function itSupportsTheCorrectTypesWithStringFormat(): void
    {
        self::assertSame([MessageInterface::class => true], $this->normalizer->getSupportedTypes('json'));
    }

    #[Test]
    public function itSupportsTheCorrectTypeOnRuntimeCheck(): void
    {
        self::assertTrue($this->normalizer->supportsDenormalization([], MessageInterface::class));
        self::assertFalse($this->normalizer->supportsDenormalization([], stdClass::class));
    }

    #[Test]
    public function theDenormalizationFailsOnNonArrayInput(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->normalizer->denormalize('', UserMessage::class);
    }

    #[Test]
    public function theDenormalizationFailsOnArrayMissingContent(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->normalizer->denormalize(['foo'], UserMessage::class);
    }

    #[Test]
    public function theNormalizationOfJustAStringWorks(): void
    {
        $obj = $this->normalizer->denormalize(['role' => 'user', 'content' => 'foo bar baz'], UserMessage::class);

        self::assertCount(1, $obj->content);
        self::assertInstanceOf(Text::class, $obj->content[0]);
        self::assertSame('foo bar baz', $obj->content[0]->text);
    }

    #[Test]
    public function thatNormalizationOfMixedContentWorks(): void
    {
        $content = [
            'role' => 'user',
            'content' => [
                ['type' => 'text', 'text' => 'foo bar baz'],
                ['type' => 'image_url', 'image_url' => ['url' => 'baz foo bar']],
            ],
        ];

        $obj = $this->normalizer->denormalize($content, UserMessage::class);

        self::assertCount(2, $obj->content);

        self::assertInstanceOf(Text::class, $obj->content[0]);
        self::assertSame('foo bar baz', $obj->content[0]->text);

        self::assertInstanceOf(Image::class, $obj->content[1]);
        self::assertSame('baz foo bar', $obj->content[1]->url);
    }

    #[Test]
    public function aSystemMessageIsDenormalized(): void
    {
        $this->denormalizer->expects($this->once())
            ->method('denormalize')
            ->with(
                self::callback(static function (mixed $data): true {
                    self::assertIsArray($data);
                    self::assertSame(['role' => 'system'], $data);

                    return true;
                }),
                self::callback(static function (string $type): true {
                    self::assertSame(SystemMessage::class, $type);

                    return true;
                }),
            )
            ->willReturn(new SystemMessage('foo'));

        $obj = $this->normalizer->denormalize(['role' => 'system'], MessageInterface::class);

        self::assertInstanceOf(SystemMessage::class, $obj);
    }

    #[Test]
    public function anAssistantMessageIsDenormalized(): void
    {
        $this->denormalizer->expects($this->once())
            ->method('denormalize')
            ->with(
                self::callback(static function (mixed $data): true {
                    self::assertIsArray($data);
                    self::assertSame(['role' => 'assistant'], $data);

                    return true;
                }),
                self::callback(static function (string $type): true {
                    self::assertSame(AssistantMessage::class, $type);

                    return true;
                }),
            )
            ->willReturn(new AssistantMessage('foo'));

        $obj = $this->normalizer->denormalize(['role' => 'assistant'], MessageInterface::class);

        self::assertInstanceOf(AssistantMessage::class, $obj);
    }

    #[Test]
    public function aToolCallMesageMessageIsDenormalized(): void
    {
        $this->denormalizer->expects($this->once())
            ->method('denormalize')
            ->with(
                self::callback(static function (mixed $data): true {
                    self::assertIsArray($data);
                    self::assertSame(['role' => 'tool'], $data);

                    return true;
                }),
                self::callback(static function (string $type): true {
                    self::assertSame(ToolCallMessage::class, $type);

                    return true;
                }),
            )
            ->willReturn(new ToolCallMessage(new ToolCall('bar-12', 'bar'), 'foo'));

        $obj = $this->normalizer->denormalize(['role' => 'tool'], MessageInterface::class);

        self::assertInstanceOf(ToolCallMessage::class, $obj);
    }
}
