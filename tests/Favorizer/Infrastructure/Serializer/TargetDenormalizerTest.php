<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Favorizer\Infrastructure\Serializer;

use ChronicleKeeper\Favorizer\Domain\ValueObject\ChatConversationTarget;
use ChronicleKeeper\Favorizer\Domain\ValueObject\LibraryDocumentTarget;
use ChronicleKeeper\Favorizer\Domain\ValueObject\LibraryImageTarget;
use ChronicleKeeper\Favorizer\Domain\ValueObject\Target;
use ChronicleKeeper\Favorizer\Domain\ValueObject\WorldItemTarget;
use ChronicleKeeper\Favorizer\Infrastructure\Serializer\TargetDenormalizer;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(TargetDenormalizer::class)]
#[Small]
class TargetDenormalizerTest extends TestCase
{
    #[Test]
    public function denormalizeChatConversationTarget(): void
    {
        $denormalizer = new TargetDenormalizer();
        $target       = $denormalizer->denormalize(
            [
                'type' => 'ChatConversationTarget',
                'id' => 'f3ce2cce-888d-4812-8470-72cdd96faf4c',
                'title' => 'Chat Conversation',
            ],
            Target::class,
        );

        self::assertInstanceOf(ChatConversationTarget::class, $target);
        self::assertSame('f3ce2cce-888d-4812-8470-72cdd96faf4c', $target->getId());
        self::assertSame('Chat Conversation', $target->getTitle());
    }

    #[Test]
    public function denormalizeLibraryDocumentTarget(): void
    {
        $denormalizer = new TargetDenormalizer();
        $target       = $denormalizer->denormalize(
            [
                'type' => 'LibraryDocumentTarget',
                'id' => 'f3ce2cce-888d-4812-8470-72cdd96faf4c',
                'title' => 'Document Title',
            ],
            Target::class,
        );

        self::assertInstanceOf(LibraryDocumentTarget::class, $target);
        self::assertSame('f3ce2cce-888d-4812-8470-72cdd96faf4c', $target->getId());
        self::assertSame('Document Title', $target->getTitle());
    }

    #[Test]
    public function denormalizeLibraryImageTarget(): void
    {
        $denormalizer = new TargetDenormalizer();
        $target       = $denormalizer->denormalize(
            [
                'type' => 'LibraryImageTarget',
                'id' => 'f3ce2cce-888d-4812-8470-72cdd96faf4c',
                'title' => 'Image Title',
            ],
            Target::class,
        );

        self::assertInstanceOf(LibraryImageTarget::class, $target);
        self::assertSame('f3ce2cce-888d-4812-8470-72cdd96faf4c', $target->getId());
        self::assertSame('Image Title', $target->getTitle());
    }

    #[Test]
    public function denormalizeWorldItemTarget(): void
    {
        $denormalizer = new TargetDenormalizer();
        $target       = $denormalizer->denormalize(
            [
                'type' => 'WorldItemTarget',
                'id' => 'f3ce2cce-888d-4812-8470-72cdd96faf4c',
                'title' => 'Example Item',
            ],
            Target::class,
        );

        self::assertInstanceOf(WorldItemTarget::class, $target);
        self::assertSame('f3ce2cce-888d-4812-8470-72cdd96faf4c', $target->getId());
        self::assertSame('Example Item', $target->getTitle());
    }

    #[Test]
    public function denormalizeUnknownTargetType(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Unknown target type.');

        $denormalizer = new TargetDenormalizer();
        $denormalizer->denormalize(
            [
                'type' => 'UnknownTarget',
                'id' => 'f3ce2cce-888d-4812-8470-72cdd96faf4c',
                'title' => 'Unknown Target',
            ],
            Target::class,
        );
    }

    #[Test]
    public function denormalizeWithoutType(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Target type is missing.');

        $denormalizer = new TargetDenormalizer();
        $denormalizer->denormalize(
            [
                'id' => 'f3ce2cce-888d-4812-8470-72cdd96faf4c',
                'title' => 'Unknown Target',
            ],
            Target::class,
        );
    }

    #[Test]
    public function supportsDenormalization(): void
    {
        $denormalizer = new TargetDenormalizer();

        self::assertTrue($denormalizer->supportsDenormalization([], Target::class));
        self::assertFalse($denormalizer->supportsDenormalization([], 'UnknownClass'));
    }

    #[Test]
    public function itHasTheCorrectSupportedTypes(): void
    {
        $denormalizer = new TargetDenormalizer();

        self::assertSame([Target::class => true], $denormalizer->getSupportedTypes(null));
        self::assertSame([Target::class => true], $denormalizer->getSupportedTypes('json'));
    }
}
