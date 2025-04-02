<?php

declare(strict_types=1);

namespace ChronicleKeeper\Shared\Infrastructure\LLMChain;

use ChronicleKeeper\Settings\Application\SettingsHandler;
use PhpLlm\LlmChain\Chain\Toolbox\Toolbox;
use PhpLlm\LlmChain\Chain\Toolbox\ToolboxInterface;
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

    public function create(): ToolboxInterface
    {
        return new SettingsToolbox(
            $this->settingsHandler,
            Toolbox::create(...$this->tools),
        );
    }
}
