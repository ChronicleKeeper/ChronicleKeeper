<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Shared\Infrastructure\LLMChain\Serializer;

use ChronicleKeeper\Shared\Infrastructure\LLMChain\Serializer\LLMUserMessageDenormalizer;
use PhpLlm\LlmChain\Message\AssistantMessage;
use PhpLlm\LlmChain\Message\Content\Image;
use PhpLlm\LlmChain\Message\Content\Text;
use PhpLlm\LlmChain\Message\MessageInterface;
use PhpLlm\LlmChain\Message\SystemMessage;
use PhpLlm\LlmChain\Message\ToolCallMessage;
use PhpLlm\LlmChain\Message\UserMessage;
use PhpLlm\LlmChain\Response\ToolCall;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use stdClass;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Webmozart\Assert\InvalidArgumentException;

#[CoversClass(LLMUserMessageDenormalizer::class)]
#[UsesClass(SystemMessage::class)]
#[UsesClass(AssistantMessage::class)]
#[UsesClass(ToolCallMessage::class)]
#[UsesClass(UserMessage::class)]
#[UsesClass(Text::class)]
#[UsesClass(Image::class)]
#[UsesClass(ToolCall::class)]
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

    public function testThatItSupportsTheCorrectTypesWithNullFormat(): void
    {
        self::assertSame([MessageInterface::class => true], $this->normalizer->getSupportedTypes(null));
    }

    public function testThatItSupportsTheCorrectTypesWithStringFormat(): void
    {
        self::assertSame([MessageInterface::class => true], $this->normalizer->getSupportedTypes('json'));
    }

    public function testThatItSupportsTheCorrectTypeOnRuntimeCheck(): void
    {
        self::assertTrue($this->normalizer->supportsDenormalization([], MessageInterface::class));
        self::assertFalse($this->normalizer->supportsDenormalization([], stdClass::class));
    }

    public function testThatDenormalizationFailsOnNonArrayInput(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->normalizer->denormalize('', UserMessage::class);
    }

    public function testThatDenormalizationFailsOnArrayMissingContent(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->normalizer->denormalize(['foo'], UserMessage::class);
    }

    public function testThatTheNormalizationOfJustAStringWorks(): void
    {
        $obj = $this->normalizer->denormalize(['role' => 'user', 'content' => 'foo bar baz'], UserMessage::class);

        self::assertInstanceOf(UserMessage::class, $obj);
        self::assertCount(1, $obj->content);
        self::assertInstanceOf(Text::class, $obj->content[0]);
        self::assertSame('foo bar baz', $obj->content[0]->text);
    }

    public function testThatNormalizationOfMixedContentWorks(): void
    {
        $content = [
            'role' => 'user',
            'content' => [
                ['type' => 'text', 'text' => 'foo bar baz'],
                ['type' => 'image_url', 'image_url' => ['url' => 'baz foo bar']],
            ],
        ];

        $obj = $this->normalizer->denormalize($content, UserMessage::class);

        self::assertInstanceOf(UserMessage::class, $obj);
        self::assertCount(2, $obj->content);

        self::assertInstanceOf(Text::class, $obj->content[0]);
        self::assertSame('foo bar baz', $obj->content[0]->text);

        self::assertInstanceOf(Image::class, $obj->content[1]);
        self::assertSame('baz foo bar', $obj->content[1]->url);
    }

    public function testThatASystemMessageIsDenormalized(): void
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

    public function testThatAnAssistantMessageIsDenormalized(): void
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

    public function testThatAToolCallMesageMessageIsDenormalized(): void
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
