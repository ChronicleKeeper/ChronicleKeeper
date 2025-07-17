<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Shared\Infrastructure\LLMChain;

use ArrayIterator;
use ChronicleKeeper\Settings\Application\SettingsHandler;
use ChronicleKeeper\Shared\Infrastructure\LLMChain\SettingsToolbox;
use ChronicleKeeper\Shared\Infrastructure\LLMChain\ToolboxFactory;
use ChronicleKeeper\Test\Shared\Infrastructure\LLMChain\Stub\ExampleTool;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(ToolboxFactory::class)]
#[UsesClass(SettingsToolbox::class)]
#[Small]
class ToolboxFactoryTest extends TestCase
{
    #[Test]
    public function createWithoutTools(): void
    {
        $toolboxFactory = new ToolboxFactory(self::createStub(SettingsHandler::class));
        $toolbox        = $toolboxFactory->create();

        self::assertEmpty($toolbox->getTools());
    }

    #[Test]
    public function createWithTools(): void
    {
        $toolboxFactory = new ToolboxFactory(self::createStub(SettingsHandler::class), [new ExampleTool()]);
        $toolbox        = $toolboxFactory->create();

        self::assertCount(1, $toolbox->getTools());
    }

    #[Test]
    public function createWithToolsFromIterator(): void
    {
        $toolboxFactory = new ToolboxFactory(
            self::createStub(SettingsHandler::class),
            new ArrayIterator([new ExampleTool()]),
        );
        $toolbox        = $toolboxFactory->create();

        self::assertCount(1, $toolbox->getTools());
    }
}
