<?php

declare(strict_types=1);

namespace ChronicleKeeper\Shared\Infrastructure\LLMChain;

use ChronicleKeeper\Settings\Application\SettingsHandler;
use PhpLlm\LlmChain\Chain;
use PhpLlm\LlmChain\EmbeddingsModel;
use PhpLlm\LlmChain\OpenAI\Model\Embeddings;
use PhpLlm\LlmChain\OpenAI\Model\Gpt as GptModel;
use PhpLlm\LlmChain\OpenAI\Platform;
use PhpLlm\LlmChain\OpenAI\Platform\OpenAI;
use PhpLlm\LlmChain\ToolBox\ChainProcessor;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class LLMChainFactory
{
    private Chain $chain;

    public function __construct(
        private readonly HttpClientInterface $httpClient,
        private readonly SettingsHandler $settingsHandler,
        private readonly ToolboxFactory $toolboxFactory,
    ) {
    }

    public function create(): Chain
    {
        if (isset($this->chain)) {
            return $this->chain;
        }

        $languageModel = new GptModel($this->createPlatform());
        $toolProcessor = new ChainProcessor($this->toolboxFactory->create());
        $this->chain   = new Chain($languageModel, [$toolProcessor], [$toolProcessor]);

        return $this->chain;
    }

    public function createPlatform(): Platform
    {
        $settings = $this->settingsHandler->get();

        return new OpenAI($this->httpClient, $settings->getApplication()->openAIApiKey ?? '');
    }

    public function createEmbeddings(): EmbeddingsModel
    {
        return new Embeddings($this->createPlatform());
    }
}
