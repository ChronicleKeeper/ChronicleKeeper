<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Chat\Infrastructure\Serializer;

use ChronicleKeeper\Chat\Domain\Entity\ExtendedMessage;
use ChronicleKeeper\Chat\Infrastructure\Serializer\ExtendedMessageDenormalizer;
use ChronicleKeeper\Library\Domain\Entity\Document;
use ChronicleKeeper\Library\Domain\Entity\Image;
use PhpLlm\LlmChain\Message\MessageInterface;
use PhpLlm\LlmChain\Message\SystemMessage;
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
    public function itFailsOnIncompleteArray(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->normalizer->denormalize(['foo'], ExtendedMessage::class);
    }

    #[Test]
    public function theNormalizationWorksAsExcepted(): void
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
                'id' => '12345',
                'message' => ['content' => 'foo'],
                'documents' => ['document' => 'bar'],
                'images' => ['image' => 'baz'],
                'calledTools' => ['calledTools' => 'qis'],
            ],
            ExtendedMessage::class,
        );

        self::assertInstanceOf(ExtendedMessage::class, $obj);
        self::assertInstanceOf(MessageInterface::class, $obj->message);
        self::assertSame('12345', $obj->id);
        self::assertSame(['document' => 'bar'], $obj->documents);
        self::assertSame(['image' => 'baz'], $obj->images);
        self::assertSame(['calledTools' => 'qis'], $obj->calledTools);
    }
}
