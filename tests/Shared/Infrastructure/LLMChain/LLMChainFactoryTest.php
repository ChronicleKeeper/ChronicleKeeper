<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Shared\Infrastructure\LLMChain;

use ChronicleKeeper\Settings\Application\SettingsHandler;
use ChronicleKeeper\Shared\Infrastructure\LLMChain\LLMChainFactory;
use ChronicleKeeper\Shared\Infrastructure\LLMChain\ToolboxFactory;
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
        $httpClient = $this->createMock(HttpClientInterface::class);

        $settingsHandler = $this->createMock(SettingsHandler::class);
        $settingsHandler->expects($this->once())->method('get');

        $toolboxFactory = $this->createMock(ToolboxFactory::class);
        $toolboxFactory->expects($this->once())->method('create');

        $llmChainFactory = new LLMChainFactory($httpClient, $settingsHandler, $toolboxFactory);
        $llmChainFactory->create();
    }

    #[Test]
    public function createPlatform(): void
    {
        $httpClient = $this->createMock(HttpClientInterface::class);

        $settingsHandler = $this->createMock(SettingsHandler::class);
        $settingsHandler->expects($this->once())->method('get');

        $toolboxFactory = $this->createMock(ToolboxFactory::class);

        $llmChainFactory = new LLMChainFactory($httpClient, $settingsHandler, $toolboxFactory);
        $llmChainFactory->createPlatform();
    }

    #[Test]
    public function createEmbeddings(): void
    {
        $httpClient = $this->createMock(HttpClientInterface::class);

        $settingsHandler = $this->createMock(SettingsHandler::class);
        $settingsHandler->expects($this->once())->method('get');

        $toolboxFactory = $this->createMock(ToolboxFactory::class);

        $llmChainFactory = new LLMChainFactory($httpClient, $settingsHandler, $toolboxFactory);
        $llmChainFactory->createEmbeddings();
    }

    #[Test]
    public function createChainReturnsCachedChain(): void
    {
        $httpClient = $this->createMock(HttpClientInterface::class);

        $settingsHandler = $this->createMock(SettingsHandler::class);
        $settingsHandler->expects($this->once())->method('get');

        $toolboxFactory = $this->createMock(ToolboxFactory::class);
        $toolboxFactory->expects($this->once())->method('create');

        $llmChainFactory = new LLMChainFactory($httpClient, $settingsHandler, $toolboxFactory);

        self::assertSame(
            $llmChainFactory->create(),
            $llmChainFactory->create(),
        );
    }
}
