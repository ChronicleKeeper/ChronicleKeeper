<?php

declare(strict_types=1);

namespace ChronicleKeeper\Shared\Infrastructure\LLMChain;

use ChronicleKeeper\Settings\Application\SettingsHandler;
use PhpLlm\LlmChain\ToolBox\ToolAnalyzer;
use PhpLlm\LlmChain\ToolBox\ToolBox;
use PhpLlm\LlmChain\ToolBox\ToolBoxInterface;
use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;
use Symfony\Component\DependencyInjection\Attribute\Lazy;
use Traversable;

use function iterator_to_array;

#[Lazy]
class ToolboxFactory
{
    /** @var object[] */
    private readonly array $tools;

    /** @param object[] $tools */
    public function __construct(
        private readonly SettingsHandler $settingsHandler,
        #[AutowireIterator('llm_chain.tool')]
        iterable $tools = [],
    ) {
        $this->tools = $tools instanceof Traversable ? iterator_to_array($tools) : $tools;
    }

    public function create(): ToolBoxInterface
    {
        return new SettingsToolBox(
            $this->settingsHandler,
            new ToolBox(new ToolAnalyzer(), $this->tools),
        );
    }
}
