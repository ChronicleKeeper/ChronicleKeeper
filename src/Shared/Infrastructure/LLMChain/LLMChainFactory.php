<?php

declare(strict_types=1);

namespace ChronicleKeeper\Shared\Infrastructure\LLMChain;

use ChronicleKeeper\Settings\Application\SettingsHandler;
use PhpLlm\LlmChain\Bridge\OpenAI\Embeddings;
use PhpLlm\LlmChain\Bridge\OpenAI\GPT;
use PhpLlm\LlmChain\Bridge\OpenAI\PlatformFactory;
use PhpLlm\LlmChain\Chain;
use PhpLlm\LlmChain\Chain\ToolBox\ChainProcessor;
use PhpLlm\LlmChain\Model\EmbeddingsModel;
use PhpLlm\LlmChain\PlatformInterface;

class LLMChainFactory
{
    private Chain $chain;

    public function __construct(
        private readonly SettingsHandler $settingsHandler,
        private readonly ToolboxFactory $toolboxFactory,
    ) {
    }

    public function create(): Chain
    {
        if (isset($this->chain)) {
            return $this->chain;
        }

        $toolProcessor = new ChainProcessor($this->toolboxFactory->create());
        $this->chain   = new Chain($this->createPlatform(), new GPT(), [$toolProcessor], [$toolProcessor]);

        return $this->chain;
    }

    public function createPlatform(): PlatformInterface
    {
        return PlatformFactory::create($this->settingsHandler->get()->getApplication()->openAIApiKey ?? '');
    }

    public function createEmbeddingsModel(): EmbeddingsModel
    {
        return new Embeddings();
    }
}
