<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\ImageGenerator\Infrastructure\Serializer;

use ChronicleKeeper\ImageGenerator\Domain\Entity\GeneratorRequest;
use ChronicleKeeper\ImageGenerator\Infrastructure\Serializer\GeneratorRequestDenormalizer;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(GeneratorRequestDenormalizer::class)]
#[Small]
class GeneratorRequestDenormalizerTest extends TestCase
{
    #[Test]
    public function correctSupportedTypes(): void
    {
        $denormalizer = new GeneratorRequestDenormalizer();

        self::assertTrue($denormalizer->supportsDenormalization([], GeneratorRequest::class));
        self::assertFalse($denormalizer->supportsDenormalization([], 'foo'));
    }

    #[Test]
    public function deliveredSupportedTypesAreCorrect(): void
    {
        $denormalizer = new GeneratorRequestDenormalizer();

        self::assertSame([GeneratorRequest::class => true], $denormalizer->getSupportedTypes(null));
    }

    #[Test]
    public function objectIsCreatable(): void
    {
        $denormalizer = new GeneratorRequestDenormalizer();

        self::assertInstanceOf(GeneratorRequestDenormalizer::class, $denormalizer);
    }

    #[Test]
    public function denormalizeWithoutOptimizedPrompt(): void
    {
        $data = [
            'title' => 'foo',
            'userInput' => 'bar',
            'id' => '123-123',
            'prompt' => null,
        ];

        $obj = (new GeneratorRequestDenormalizer())->denormalize($data, GeneratorRequest::class);

        self::assertInstanceOf(GeneratorRequest::class, $obj);
        self::assertSame('foo', $obj->title);
        self::assertSame('bar', $obj->userInput->prompt);
        self::assertSame('123-123', $obj->id);
        self::assertNull($obj->prompt);
    }

    #[Test]
    public function denormalizeWithOptimizedPrompt(): void
    {
        $data = [
            'title' => 'foo',
            'userInput' => 'bar',
            'id' => '123-123',
            'prompt' => 'baz',
        ];

        $obj = (new GeneratorRequestDenormalizer())->denormalize($data, GeneratorRequest::class);

        self::assertInstanceOf(GeneratorRequest::class, $obj);
        self::assertSame('foo', $obj->title);
        self::assertSame('bar', $obj->userInput->prompt);
        self::assertSame('123-123', $obj->id);
        self::assertSame('baz', $obj->prompt?->prompt);
    }
}
