<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Chat\Domain\ValueObject;

use ChronicleKeeper\Chat\Domain\ValueObject\FunctionDebug;
use Generator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(FunctionDebug::class)]
#[Small]
class FunctionDebugTest extends TestCase
{
    public static function functionDebugProvider(): Generator
    {
        yield 'no arguments, no result' => ['tool', [], null];
        yield 'with arguments, no result' => ['tool', ['arg1' => 'value1', 'arg2' => 'value2'], null];
        yield 'no arguments, with result' => ['tool', [], 'result'];
        yield 'with arguments and result' => ['tool', ['arg1' => 'value1', 'arg2' => 'value2'], 'result'];
        yield 'with arguments and null result' => ['tool', ['arg1' => 'value1', 'arg2' => 'value2'], null];
        yield 'empty tool name' => ['', ['arg1' => 'value1'], 'result'];
        yield 'empty arguments array' => ['tool', [], 'result'];
        yield 'null result explicitly' => ['tool', ['arg1' => 'value1'], null];
        yield 'special characters in tool name' => ['t@#l$', ['arg1' => 'value1'], 'result'];
        yield 'nested arguments' => ['tool', ['arg1' => ['nestedArg1' => 'nestedValue1']], 'result'];
    }

    /** @param array<string, mixed> $arguments */
    #[Test]
    #[DataProvider('functionDebugProvider')]
    public function itCanBeConstructed(
        string $tool,
        array $arguments,
        string|null $result,
    ): void {
        $functionDebug = new FunctionDebug($tool, $arguments, $result);

        self::assertSame($tool, $functionDebug->tool);
        self::assertSame($arguments, $functionDebug->arguments);
        self::assertSame($result, $functionDebug->result);
    }
}
