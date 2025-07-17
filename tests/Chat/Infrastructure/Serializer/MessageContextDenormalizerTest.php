<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Chat\Infrastructure\Serializer;

use ChronicleKeeper\Chat\Domain\ValueObject\MessageContext;
use ChronicleKeeper\Chat\Infrastructure\Serializer\MessageContextDenormalizer;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\Exception\InvalidArgumentException;

#[CoversClass(MessageContextDenormalizer::class)]
#[Small]
class MessageContextDenormalizerTest extends TestCase
{
    private MessageContextDenormalizer $normalizer;

    protected function setUp(): void
    {
        $this->normalizer = new MessageContextDenormalizer();
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
        self::assertTrue($this->normalizer->supportsDenormalization([], MessageContext::class));
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

        $this->normalizer->denormalize('not an array', MessageContext::class);
    }

    #[Test]
    public function itDenormalizesEmptyArray(): void
    {
        $result = $this->normalizer->denormalize([], MessageContext::class);

        self::assertSame([], $result->documents);
        self::assertSame([], $result->images);
    }

    #[Test]
    public function itDenormalizesWithoutContextFlags(): void
    {
        $data = [
            'documents' => [
                ['id' => 'doc1', 'type' => 'document', 'title' => 'Document 1'],
            ],
            'images' => [
                ['id' => 'img1', 'type' => 'image', 'title' => 'Image 1'],
            ],
        ];

        $result = $this->normalizer->denormalize($data, MessageContext::class);

        self::assertSame([], $result->documents); // Should be empty without context flags
        self::assertSame([], $result->images); // Should be empty without context flags
    }

    #[Test]
    public function itDenormalizesWithDocumentsWhenContextFlagIsSet(): void
    {
        $data = [
            'documents' => [
                ['id' => 'doc1', 'type' => 'document', 'title' => 'Document 1'],
                ['id' => 'doc2', 'type' => 'document', 'title' => 'Document 2'],
            ],
        ];

        $context = [MessageContextDenormalizer::WITH_CONTEXT_DOCUMENTS => true];

        $result = $this->normalizer->denormalize($data, MessageContext::class, null, $context);

        self::assertCount(2, $result->documents);
        self::assertSame('doc1', $result->documents[0]->id);
        self::assertSame('document', $result->documents[0]->type);
        self::assertSame('Document 1', $result->documents[0]->title);
        self::assertSame('doc2', $result->documents[1]->id);
        self::assertSame('document', $result->documents[1]->type);
        self::assertSame('Document 2', $result->documents[1]->title);
    }

    #[Test]
    public function itDenormalizesWithImagesWhenContextFlagIsSet(): void
    {
        $data = [
            'images' => [
                ['id' => 'img1', 'type' => 'image', 'title' => 'Image 1'],
                ['id' => 'img2', 'type' => 'image', 'title' => 'Image 2'],
            ],
        ];

        $context = [MessageContextDenormalizer::WITH_CONTEXT_IMAGES => true];

        $result = $this->normalizer->denormalize($data, MessageContext::class, null, $context);

        self::assertCount(2, $result->images);
        self::assertSame('img1', $result->images[0]->id);
        self::assertSame('image', $result->images[0]->type);
        self::assertSame('Image 1', $result->images[0]->title);
        self::assertSame('img2', $result->images[1]->id);
        self::assertSame('image', $result->images[1]->type);
        self::assertSame('Image 2', $result->images[1]->title);
    }

    #[Test]
    public function itDenormalizesWithBothDocumentsAndImagesWhenBothFlagsAreSet(): void
    {
        $data = [
            'documents' => [
                ['id' => 'doc1', 'type' => 'document', 'title' => 'Document 1'],
            ],
            'images' => [
                ['id' => 'img1', 'type' => 'image', 'title' => 'Image 1'],
            ],
        ];

        $context = [
            MessageContextDenormalizer::WITH_CONTEXT_DOCUMENTS => true,
            MessageContextDenormalizer::WITH_CONTEXT_IMAGES => true,
        ];

        $result = $this->normalizer->denormalize($data, MessageContext::class, null, $context);

        self::assertCount(1, $result->documents);
        self::assertCount(1, $result->images);
        self::assertSame('doc1', $result->documents[0]->id);
        self::assertSame('img1', $result->images[0]->id);
    }

    #[Test]
    public function itHandlesMissingDocumentsOrImagesInData(): void
    {
        $data = []; // Empty data

        $context = [
            MessageContextDenormalizer::WITH_CONTEXT_DOCUMENTS => true,
            MessageContextDenormalizer::WITH_CONTEXT_IMAGES => true,
        ];

        $result = $this->normalizer->denormalize($data, MessageContext::class, null, $context);

        self::assertSame([], $result->documents);
        self::assertSame([], $result->images);
    }
}
