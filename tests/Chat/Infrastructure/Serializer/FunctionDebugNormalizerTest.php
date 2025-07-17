<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Chat\Infrastructure\Serializer;

use ChronicleKeeper\Chat\Domain\ValueObject\FunctionDebug;
use ChronicleKeeper\Chat\Infrastructure\Serializer\FunctionDebugNormalizer;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\Exception\InvalidArgumentException;

#[CoversClass(FunctionDebugNormalizer::class)]
#[Small]
class FunctionDebugNormalizerTest extends TestCase
{
    private FunctionDebugNormalizer $normalizer;

    protected function setUp(): void
    {
        $this->normalizer = new FunctionDebugNormalizer();
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
        $functionDebug = new FunctionDebug('tool', ['arg1' => 'value1'], 'result');
        self::assertTrue($this->normalizer->supportsNormalization($functionDebug));
    }

    #[Test]
    public function itDoesNotSupportWrongTypes(): void
    {
        self::assertFalse($this->normalizer->supportsNormalization('string'));
        self::assertFalse($this->normalizer->supportsNormalization(123));
        self::assertFalse($this->normalizer->supportsNormalization([]));
    }

    #[Test]
    public function itFailsOnNonFunctionDebugInput(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Expected Instance of "' . FunctionDebug::class . '"');

        $this->normalizer->normalize('not a function debug');
    }

    #[Test]
    public function itNormalizesFunctionDebugWithAllProperties(): void
    {
        $functionDebug = new FunctionDebug('search_tool', ['query' => 'test'], 'search result');

        $result = $this->normalizer->normalize($functionDebug);

        self::assertArrayHasKey('tool', $result);
        self::assertArrayHasKey('arguments', $result);
        self::assertArrayHasKey('result', $result);
        self::assertSame('search_tool', $result['tool']);
        self::assertSame(['query' => 'test'], $result['arguments']);
        self::assertSame('search result', $result['result']);
    }

    #[Test]
    public function itNormalizesFunctionDebugWithNullResult(): void
    {
        $functionDebug = new FunctionDebug('search_tool', ['query' => 'test'], null);

        $result = $this->normalizer->normalize($functionDebug);

        self::assertArrayHasKey('tool', $result);
        self::assertArrayHasKey('arguments', $result);
        self::assertArrayHasKey('result', $result);
        self::assertSame('search_tool', $result['tool']);
        self::assertSame(['query' => 'test'], $result['arguments']);
        self::assertNull($result['result']);
    }

    #[Test]
    public function itNormalizesFunctionDebugWithEmptyArguments(): void
    {
        $functionDebug = new FunctionDebug('simple_tool', [], 'result');

        $result = $this->normalizer->normalize($functionDebug);

        self::assertArrayHasKey('tool', $result);
        self::assertArrayHasKey('arguments', $result);
        self::assertArrayHasKey('result', $result);
        self::assertSame('simple_tool', $result['tool']);
        self::assertSame([], $result['arguments']);
        self::assertSame('result', $result['result']);
    }

    #[Test]
    public function itNormalizesFunctionDebugWithComplexArguments(): void
    {
        $complexArgs   = [
            'query' => 'test',
            'filters' => ['type' => 'document'],
            'limit' => 10,
            'nested' => ['deep' => ['value' => true]],
        ];
        $functionDebug = new FunctionDebug('complex_tool', $complexArgs, 'complex result');

        $result = $this->normalizer->normalize($functionDebug);

        self::assertSame('complex_tool', $result['tool']);
        self::assertSame($complexArgs, $result['arguments']);
        self::assertSame('complex result', $result['result']);
    }
}
