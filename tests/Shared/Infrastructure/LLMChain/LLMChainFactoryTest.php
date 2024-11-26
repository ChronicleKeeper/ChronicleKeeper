<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Shared\Infrastructure\LLMChain;

use ChronicleKeeper\Settings\Application\SettingsHandler;
use ChronicleKeeper\Settings\Domain\ValueObject\Settings;
use ChronicleKeeper\Settings\Domain\ValueObject\Settings\Application;
use ChronicleKeeper\Shared\Infrastructure\LLMChain\LLMChainFactory;
use ChronicleKeeper\Shared\Infrastructure\LLMChain\ToolboxFactory;
use ChronicleKeeper\Test\Settings\Domain\ValueObject\SettingsBuilder;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(LLMChainFactory::class)]
#[Small]
class LLMChainFactoryTest extends TestCase
{
    #[Test]
    public function createChain(): void
    {
        $settingsHandler = $this->createMock(SettingsHandler::class);
        $settingsHandler->expects($this->once())
            ->method('get')
            ->willReturn($this->getSettings());

        $toolboxFactory = $this->createMock(ToolboxFactory::class);
        $toolboxFactory->expects($this->once())->method('create');

        $llmChainFactory = new LLMChainFactory($settingsHandler, $toolboxFactory);
        $llmChainFactory->create();
    }

    #[Test]
    public function createPlatform(): void
    {
        $settingsHandler = $this->createMock(SettingsHandler::class);
        $settingsHandler->expects($this->once())->method('get')->willReturn($this->getSettings());

        $toolboxFactory = $this->createMock(ToolboxFactory::class);

        $llmChainFactory = new LLMChainFactory($settingsHandler, $toolboxFactory);
        $llmChainFactory->createPlatform();
    }

    #[Test]
    public function createChainReturnsCachedChain(): void
    {
        $settingsHandler = $this->createMock(SettingsHandler::class);
        $settingsHandler->expects($this->once())->method('get')->willReturn($this->getSettings());

        $toolboxFactory = $this->createMock(ToolboxFactory::class);
        $toolboxFactory->expects($this->once())->method('create');

        $llmChainFactory = new LLMChainFactory($settingsHandler, $toolboxFactory);

        self::assertSame(
            $llmChainFactory->create(),
            $llmChainFactory->create(),
        );
    }

    private function getSettings(): Settings
    {
        return (new SettingsBuilder())
            ->withApplication(new Application('sk--api-key'))
            ->build();
    }
}
