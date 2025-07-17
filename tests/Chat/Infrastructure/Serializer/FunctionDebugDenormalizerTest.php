<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Chat\Infrastructure\Serializer;

use ChronicleKeeper\Chat\Domain\ValueObject\FunctionDebug;
use ChronicleKeeper\Chat\Infrastructure\Serializer\FunctionDebugDenormalizer;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\Exception\InvalidArgumentException;

#[CoversClass(FunctionDebugDenormalizer::class)]
#[Small]
class FunctionDebugDenormalizerTest extends TestCase
{
    private FunctionDebugDenormalizer $normalizer;

    protected function setUp(): void
    {
        $this->normalizer = new FunctionDebugDenormalizer();
    }

    protected function tearDown(): void
    {
        unset($this->normalizer);
    }

    #[Test]
    public function itSupportsTheCorrectTypesWithNullFormat(): void
    {
        self::assertSame([FunctionDebug::class => true], $this->normalizer->getSupportedTypes(null));
    }

    #[Test]
    public function itSupportsTheCorrectTypesWithStringFormat(): void
    {
        self::assertSame([FunctionDebug::class => true], $this->normalizer->getSupportedTypes('json'));
    }

    #[Test]
    public function itSupportsTheCorrectTypeOnRuntimeCheck(): void
    {
        self::assertTrue($this->normalizer->supportsDenormalization([], FunctionDebug::class));
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

        $this->normalizer->denormalize('not an array', FunctionDebug::class);
    }

    #[Test]
    public function itDenormalizesWithAllProperties(): void
    {
        $data = [
            'tool' => 'search_tool',
            'arguments' => ['query' => 'test'],
            'result' => 'search result',
        ];

        $result = $this->normalizer->denormalize($data, FunctionDebug::class);

        self::assertSame('search_tool', $result->tool);
        self::assertSame(['query' => 'test'], $result->arguments);
        self::assertSame('search result', $result->result);
    }

    #[Test]
    public function itDenormalizesWithNullResult(): void
    {
        $data = [
            'tool' => 'search_tool',
            'arguments' => ['query' => 'test'],
            'result' => null,
        ];

        $result = $this->normalizer->denormalize($data, FunctionDebug::class);

        self::assertSame('search_tool', $result->tool);
        self::assertSame(['query' => 'test'], $result->arguments);
        self::assertNull($result->result);
    }

    #[Test]
    public function itDenormalizesWithMissingResult(): void
    {
        $data = [
            'tool' => 'search_tool',
            'arguments' => ['query' => 'test'],
        ];

        $result = $this->normalizer->denormalize($data, FunctionDebug::class);

        self::assertSame('search_tool', $result->tool);
        self::assertSame(['query' => 'test'], $result->arguments);
        self::assertNull($result->result);
    }

    #[Test]
    public function itDenormalizesWithEmptyArguments(): void
    {
        $data = [
            'tool' => 'simple_tool',
            'arguments' => [],
            'result' => 'result',
        ];

        $result = $this->normalizer->denormalize($data, FunctionDebug::class);

        self::assertSame('simple_tool', $result->tool);
        self::assertSame([], $result->arguments);
        self::assertSame('result', $result->result);
    }

    #[Test]
    public function itDenormalizesWithComplexArguments(): void
    {
        $complexArgs = [
            'query' => 'test',
            'filters' => ['type' => 'document'],
            'limit' => 10,
            'nested' => ['deep' => ['value' => true]],
        ];
        $data        = [
            'tool' => 'complex_tool',
            'arguments' => $complexArgs,
            'result' => 'complex result',
        ];

        $result = $this->normalizer->denormalize($data, FunctionDebug::class);

        self::assertSame('complex_tool', $result->tool);
        self::assertSame($complexArgs, $result->arguments);
        self::assertSame('complex result', $result->result);
    }
}
