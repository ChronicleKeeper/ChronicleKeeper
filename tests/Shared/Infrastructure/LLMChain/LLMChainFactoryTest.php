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
use Symfony\Contracts\HttpClient\HttpClientInterface;

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

        $httpCllient = self::createStub(HttpClientInterface::class);

        $llmChainFactory = new LLMChainFactory($settingsHandler, $toolboxFactory, $httpCllient);
        $llmChainFactory->create();
    }

    #[Test]
    public function createPlatform(): void
    {
        $settingsHandler = $this->createMock(SettingsHandler::class);
        $settingsHandler->expects($this->once())->method('get')->willReturn($this->getSettings());

        $toolboxFactory = $this->createMock(ToolboxFactory::class);
        $httpCllient    = self::createStub(HttpClientInterface::class);

        $llmChainFactory = new LLMChainFactory($settingsHandler, $toolboxFactory, $httpCllient);
        $llmChainFactory->createPlatform();
    }

    #[Test]
    public function createChainReturnsCachedChain(): void
    {
        $settingsHandler = $this->createMock(SettingsHandler::class);
        $settingsHandler->expects($this->once())->method('get')->willReturn($this->getSettings());

        $toolboxFactory = $this->createMock(ToolboxFactory::class);
        $toolboxFactory->expects($this->once())->method('create');

        $httpCllient = self::createStub(HttpClientInterface::class);

        $llmChainFactory = new LLMChainFactory($settingsHandler, $toolboxFactory, $httpCllient);

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
