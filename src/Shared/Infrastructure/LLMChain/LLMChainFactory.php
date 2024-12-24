<?php

declare(strict_types=1);

namespace ChronicleKeeper\Shared\Infrastructure\LLMChain;

use ChronicleKeeper\ImageGenerator\Infrastructure\LLMChain\Bridge\OpenAI\DallE\ModelClient as DalleModelClient;
use ChronicleKeeper\Settings\Application\SettingsHandler;
use PhpLlm\LlmChain\Bridge\OpenAI\Embeddings\ModelClient as EmbeddingsModelClient;
use PhpLlm\LlmChain\Bridge\OpenAI\Embeddings\ResponseConverter as EmbeddingsResponseConverter;
use PhpLlm\LlmChain\Bridge\OpenAI\GPT;
use PhpLlm\LlmChain\Bridge\OpenAI\GPT\ModelClient as GPTModelClient;
use PhpLlm\LlmChain\Bridge\OpenAI\GPT\ResponseConverter as GPTResponseConverter;
use PhpLlm\LlmChain\Chain;
use PhpLlm\LlmChain\Chain\ToolBox\ChainProcessor;
use PhpLlm\LlmChain\ChainInterface;
use PhpLlm\LlmChain\Platform;
use PhpLlm\LlmChain\PlatformInterface;
use Symfony\Component\HttpClient\EventSourceHttpClient;

class LLMChainFactory
{
    private Chain $chain;

    public function __construct(
        private readonly SettingsHandler $settingsHandler,
        private readonly ToolboxFactory $toolboxFactory,
    ) {
    }

    public function create(): ChainInterface
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
        $apiKey     = $this->settingsHandler->get()->getApplication()->openAIApiKey ?? '';
        $httpClient = new EventSourceHttpClient();

        $dallEModelClient = new DalleModelClient($httpClient, $apiKey);

        return new Platform(
            [
                new GPTModelClient($httpClient, $apiKey),
                new EmbeddingsModelClient($httpClient, $apiKey),
                $dallEModelClient,
            ],
            [
                new GPTResponseConverter(),
                new EmbeddingsResponseConverter(),
                $dallEModelClient,
            ],
        );
    }
}
