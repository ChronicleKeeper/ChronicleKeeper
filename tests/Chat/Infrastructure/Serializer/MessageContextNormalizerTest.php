<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Chat\Infrastructure\Serializer;

use ChronicleKeeper\Chat\Domain\ValueObject\MessageContext;
use ChronicleKeeper\Chat\Domain\ValueObject\Reference;
use ChronicleKeeper\Chat\Infrastructure\Serializer\MessageContextNormalizer;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\Exception\InvalidArgumentException;

#[CoversClass(MessageContextNormalizer::class)]
#[Small]
class MessageContextNormalizerTest extends TestCase
{
    private MessageContextNormalizer $normalizer;

    protected function setUp(): void
    {
        $this->normalizer = new MessageContextNormalizer();
    }

    protected function tearDown(): void
    {
        unset($this->normalizer);
    }

    #[Test]
    public function itSupportsTheCorrectTypesWithNullFormat(): void
    {
        self::assertSame([MessageContext::class => true], $this->normalizer->getSupportedTypes(null));
    }

    #[Test]
    public function itSupportsTheCorrectTypesWithStringFormat(): void
    {
        self::assertSame([MessageContext::class => true], $this->normalizer->getSupportedTypes('json'));
    }

    #[Test]
    public function itSupportsTheCorrectTypeOnRuntimeCheck(): void
    {
        $context = new MessageContext();
        self::assertTrue($this->normalizer->supportsNormalization($context));
    }

    #[Test]
    public function itDoesNotSupportWrongTypes(): void
    {
        self::assertFalse($this->normalizer->supportsNormalization('string'));
        self::assertFalse($this->normalizer->supportsNormalization(123));
        self::assertFalse($this->normalizer->supportsNormalization([]));
    }

    #[Test]
    public function itFailsOnNonMessageContextInput(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Expected Instance of "' . MessageContext::class . '"');

        $this->normalizer->normalize('not a message context');
    }

    #[Test]
    public function itNormalizesEmptyMessageContext(): void
    {
        $context = new MessageContext();

        $result = $this->normalizer->normalize($context);

        self::assertArrayHasKey('documents', $result);
        self::assertArrayHasKey('images', $result);
        self::assertSame([], $result['documents']);
        self::assertSame([], $result['images']);
    }

    #[Test]
    public function itNormalizesMessageContextWithDocuments(): void
    {
        $document1 = new Reference('doc1', 'document', 'Document 1');
        $document2 = new Reference('doc2', 'document', 'Document 2');
        $context   = new MessageContext([$document1, $document2]);

        $result = $this->normalizer->normalize($context);

        self::assertArrayHasKey('documents', $result);
        self::assertArrayHasKey('images', $result);
        self::assertCount(2, $result['documents']);
        self::assertSame([], $result['images']);

        self::assertSame([
            'id' => 'doc1',
            'type' => 'document',
            'title' => 'Document 1',
        ], $result['documents'][0]);

        self::assertSame([
            'id' => 'doc2',
            'type' => 'document',
            'title' => 'Document 2',
        ], $result['documents'][1]);
    }

    #[Test]
    public function itNormalizesMessageContextWithImages(): void
    {
        $image1  = new Reference('img1', 'image', 'Image 1');
        $image2  = new Reference('img2', 'image', 'Image 2');
        $context = new MessageContext([], [$image1, $image2]);

        $result = $this->normalizer->normalize($context);

        self::assertArrayHasKey('documents', $result);
        self::assertArrayHasKey('images', $result);
        self::assertSame([], $result['documents']);
        self::assertCount(2, $result['images']);

        self::assertSame([
            'id' => 'img1',
            'type' => 'image',
            'title' => 'Image 1',
        ], $result['images'][0]);

        self::assertSame([
            'id' => 'img2',
            'type' => 'image',
            'title' => 'Image 2',
        ], $result['images'][1]);
    }

    #[Test]
    public function itNormalizesMessageContextWithBothDocumentsAndImages(): void
    {
        $document = new Reference('doc1', 'document', 'Document 1');
        $image    = new Reference('img1', 'image', 'Image 1');
        $context  = new MessageContext([$document], [$image]);

        $result = $this->normalizer->normalize($context);

        self::assertArrayHasKey('documents', $result);
        self::assertArrayHasKey('images', $result);
        self::assertCount(1, $result['documents']);
        self::assertCount(1, $result['images']);

        self::assertSame([
            'id' => 'doc1',
            'type' => 'document',
            'title' => 'Document 1',
        ], $result['documents'][0]);

        self::assertSame([
            'id' => 'img1',
            'type' => 'image',
            'title' => 'Image 1',
        ], $result['images'][0]);
    }
}
