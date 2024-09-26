<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Chat\Infrastructure\Serializer;

use ChronicleKeeper\Chat\Infrastructure\LLMChain\ExtendedMessage;
use ChronicleKeeper\Chat\Infrastructure\Serializer\ExtendedMessageDenormalizer;
use ChronicleKeeper\Library\Domain\Entity\Document;
use ChronicleKeeper\Library\Domain\Entity\Image;
use PhpLlm\LlmChain\Message\MessageInterface;
use PhpLlm\LlmChain\Message\SystemMessage;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use stdClass;
use Symfony\Component\Serializer\Serializer;
use Webmozart\Assert\InvalidArgumentException;

#[CoversClass(ExtendedMessageDenormalizer::class)]
#[UsesClass(ExtendedMessage::class)]
#[UsesClass(SystemMessage::class)]
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

    public function testThatItSupportsTheCorrectTypesWithNullFormat(): void
    {
        self::assertSame([ExtendedMessage::class => true], $this->normalizer->getSupportedTypes(null));
    }

    public function testThatItSupportsTheCorrectTypesWithStringFormat(): void
    {
        self::assertSame([ExtendedMessage::class => true], $this->normalizer->getSupportedTypes('json'));
    }

    public function testThatItSupportsTheCorrectTypeOnRuntimeCheck(): void
    {
        self::assertTrue($this->normalizer->supportsDenormalization([], ExtendedMessage::class));
        self::assertFalse($this->normalizer->supportsDenormalization([], stdClass::class));
    }

    public function testThatItFailsOnNonArrayInput(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->normalizer->denormalize('', ExtendedMessage::class);
    }

    public function testThatItFailsOnIncompleteArray(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->normalizer->denormalize(['foo'], ExtendedMessage::class);
    }

    public function testThatTheNormalizationWorksAsExcepted(): void
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

                    if ($invoker->numberOfInvocations() === 2) {
                        self::assertIsArray($data);
                        self::assertSame(['document' => 'bar'], $data);
                    }

                    if ($invoker->numberOfInvocations() === 3) {
                        self::assertIsArray($data);
                        self::assertSame(['image' => 'baz'], $data);
                    }

                    return true;
                }),
                self::callback(static function (string $type) use ($invoker): true {
                    if ($invoker->numberOfInvocations() === 1) {
                        self::assertSame(MessageInterface::class, $type);
                    }

                    if ($invoker->numberOfInvocations() === 2) {
                        self::assertSame(Document::class . '[]', $type);
                    }

                    if ($invoker->numberOfInvocations() === 3) {
                        self::assertSame(Image::class . '[]', $type);
                    }

                    return true;
                }),
            )
            ->willReturnCallback(
                static function () use ($invoker): mixed {
                    if ($invoker->numberOfInvocations() === 1) {
                        return new SystemMessage('foo');
                    }

                    if ($invoker->numberOfInvocations() === 2) {
                        return ['document' => 'bar'];
                    }

                    if ($invoker->numberOfInvocations() === 3) {
                        return ['image' => 'baz'];
                    }

                    return false;
                },
            );

        $obj = $this->normalizer->denormalize(
            [
                'message' => ['content' => 'foo'],
                'documents' => ['document' => 'bar'],
                'images' => ['image' => 'baz'],
                'calledTools' => ['calledTools' => 'qis'],
            ],
            ExtendedMessage::class,
        );

        self::assertInstanceOf(ExtendedMessage::class, $obj);
        self::assertInstanceOf(MessageInterface::class, $obj->message);
        self::assertSame(['document' => 'bar'], $obj->documents);
        self::assertSame(['image' => 'baz'], $obj->images);
        self::assertSame(['calledTools' => 'qis'], $obj->calledTools);
    }
}
