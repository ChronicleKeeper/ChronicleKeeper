<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Chat\Infrastructure\Serializer;

use ChronicleKeeper\Chat\Domain\Entity\ExtendedMessage;
use ChronicleKeeper\Chat\Infrastructure\Serializer\ExtendedMessageDenormalizer;
use PhpLlm\LlmChain\Platform\Message\MessageInterface;
use PhpLlm\LlmChain\Platform\Message\SystemMessage;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use stdClass;
use Symfony\Component\Serializer\Serializer;
use Webmozart\Assert\InvalidArgumentException;

#[CoversClass(ExtendedMessageDenormalizer::class)]
#[Small]
class ExtendedMessageDenormalizerTest extends TestCase
{
    private MockObject&Serializer $serializer;
    private ExtendedMessageDenormalizer $normalizer;

    protected function setUp(): void
    {
        $this->serializer = $this->createMock(Serializer::class);

        $this->normalizer = new ExtendedMessageDenormalizer();
        $this->normalizer->setDenormalizer($this->serializer);
    }

    protected function tearDown(): void
    {
        unset($this->serializer, $this->normalizer);
    }

    #[Test]
    public function itSupportsTheCorrectTypesWithNullFormat(): void
    {
        self::assertSame([ExtendedMessage::class => true], $this->normalizer->getSupportedTypes(null));
    }

    #[Test]
    public function itSupportsTheCorrectTypesWithStringFormat(): void
    {
        self::assertSame([ExtendedMessage::class => true], $this->normalizer->getSupportedTypes('json'));
    }

    #[Test]
    public function itSupportsTheCorrectTypeOnRuntimeCheck(): void
    {
        self::assertTrue($this->normalizer->supportsDenormalization([], ExtendedMessage::class));
        self::assertFalse($this->normalizer->supportsDenormalization([], stdClass::class));
    }

    #[Test]
    public function itFailsOnNonArrayInput(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->normalizer->denormalize('', ExtendedMessage::class);
    }

    #[Test]
    public function theNormalizationWorksAsExpected(): void
    {
        $invoker = $this->any();

        $this->serializer
            ->expects($invoker)
            ->method('denormalize')
            ->with(
                self::callback(static function (mixed $data) use ($invoker): true {
                    if ($invoker->numberOfInvocations() === 1) {
                        self::assertIsArray($data);
                        self::assertSame(['content' => 'foo'], $data);
                    }

                    return true;
                }),
                self::callback(static function (string $type) use ($invoker): true {
                    if ($invoker->numberOfInvocations() === 1) {
                        self::assertSame(MessageInterface::class, $type);
                    }

                    return true;
                }),
            )
            ->willReturnCallback(
                static function () use ($invoker): mixed {
                    if ($invoker->numberOfInvocations() === 1) {
                        return new SystemMessage('foo');
                    }

                    return false;
                },
            );

        $obj = $this->normalizer->denormalize(
            [
                'id' => '12345',
                'message' => ['content' => 'foo'],
                'context' => ['documents' => [], 'images' => []],
            ],
            ExtendedMessage::class,
        );

        self::assertSame('12345', $obj->id);
    }

    #[Test]
    public function theNormalizationWorksAsExpectedWithContextDocuments(): void
    {
        $invoker = $this->any();

        $this->serializer
            ->expects($invoker)
            ->method('denormalize')
            ->with(
                self::callback(static function (mixed $data) use ($invoker): true {
                    if ($invoker->numberOfInvocations() === 1) {
                        self::assertIsArray($data);
                        self::assertSame(['content' => 'foo'], $data);
                    }

                    return true;
                }),
                self::callback(static function (string $type) use ($invoker): true {
                    if ($invoker->numberOfInvocations() === 1) {
                        self::assertSame(MessageInterface::class, $type);
                    }

                    return true;
                }),
            )
            ->willReturnCallback(
                static function () use ($invoker): mixed {
                    if ($invoker->numberOfInvocations() === 1) {
                        return new SystemMessage('foo');
                    }

                    return false;
                },
            );

        $obj = $this->normalizer->denormalize(
            [
                'id' => '12345',
                'message' => ['content' => 'foo'],
                'context' => ['documents' => [['id' => 'foo', 'title' => 'bar', 'type' => 'document']], 'images' => []],
            ],
            ExtendedMessage::class,
            null,
            [ExtendedMessageDenormalizer::WITH_CONTEXT_DOCUMENTS => true],
        );

        self::assertSame('12345', $obj->id);

        self::assertCount(1, $obj->context->documents);
        self::assertSame('foo', $obj->context->documents[0]->id);
        self::assertSame('document', $obj->context->documents[0]->type);
        self::assertSame('bar', $obj->context->documents[0]->title);

        self::assertEmpty($obj->context->images);
    }

    #[Test]
    public function theNormalizationWorksAsExpectedWithContextImages(): void
    {
        $invoker = $this->any();

        $this->serializer
            ->expects($invoker)
            ->method('denormalize')
            ->with(
                self::callback(static function (mixed $data) use ($invoker): true {
                    if ($invoker->numberOfInvocations() === 1) {
                        self::assertIsArray($data);
                        self::assertSame(['content' => 'foo'], $data);
                    }

                    return true;
                }),
                self::callback(static function (string $type) use ($invoker): true {
                    if ($invoker->numberOfInvocations() === 1) {
                        self::assertSame(MessageInterface::class, $type);
                    }

                    return true;
                }),
            )
            ->willReturnCallback(
                static function () use ($invoker): mixed {
                    if ($invoker->numberOfInvocations() === 1) {
                        return new SystemMessage('foo');
                    }

                    return false;
                },
            );

        $obj = $this->normalizer->denormalize(
            [
                'id' => '12345',
                'message' => ['content' => 'foo'],
                'context' => ['documents' => [], 'images' => [['id' => 'foo', 'title' => 'bar', 'type' => 'image']]],
            ],
            ExtendedMessage::class,
            null,
            [ExtendedMessageDenormalizer::WITH_CONTEXT_IMAGES => true],
        );

        self::assertSame('12345', $obj->id);
        self::assertEmpty($obj->context->documents);

        self::assertCount(1, $obj->context->images);
        self::assertSame('foo', $obj->context->images[0]->id);
        self::assertSame('image', $obj->context->images[0]->type);
        self::assertSame('bar', $obj->context->images[0]->title);
    }
}
