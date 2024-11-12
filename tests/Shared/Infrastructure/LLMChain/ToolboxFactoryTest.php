<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Shared\Infrastructure\LLMChain;

use ArrayIterator;
use ChronicleKeeper\Shared\Infrastructure\LLMChain\ToolboxFactory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

#[CoversClass(ToolboxFactory::class)]
#[Small]
class ToolboxFactoryTest extends TestCase
{
    #[Test]
    public function createWithoutTools(): void
    {
        $toolboxFactory = new ToolboxFactory();
        $toolbox        = $toolboxFactory->create();

        $reflection = new ReflectionClass($toolbox);
        $property   = $reflection->getProperty('tools');
        $tools      = $property->getValue($toolbox);

        self::assertEmpty($tools);
    }

    #[Test]
    public function createWithTools(): void
    {
        $tool1 = new class {
        };
        $tool2 = new class {
        };

        $toolboxFactory = new ToolboxFactory([$tool1, $tool2]);
        $toolbox        = $toolboxFactory->create();

        $reflection = new ReflectionClass($toolbox);
        $property   = $reflection->getProperty('tools');
        $tools      = $property->getValue($toolbox);

        self::assertCount(2, $tools);
        self::assertSame($tool1, $tools[0]);
        self::assertSame($tool2, $tools[1]);
    }

    #[Test]
    public function createWithToolsFromIterator(): void
    {
        $tool1 = new class {
        };
        $tool2 = new class {
        };

        $toolboxFactory = new ToolboxFactory(new ArrayIterator([$tool1, $tool2]));
        $toolbox        = $toolboxFactory->create();

        $reflection = new ReflectionClass($toolbox);
        $property   = $reflection->getProperty('tools');
        $tools      = $property->getValue($toolbox);

        self::assertCount(2, $tools);
        self::assertSame($tool1, $tools[0]);
        self::assertSame($tool2, $tools[1]);
    }
}
