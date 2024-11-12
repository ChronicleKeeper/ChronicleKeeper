<?php

declare(strict_types=1);

namespace ChronicleKeeper\Shared\Infrastructure\LLMChain;

use ChronicleKeeper\Settings\Application\SettingsHandler;
use PhpLlm\LlmChain\Response\ToolCall;
use PhpLlm\LlmChain\ToolBox\Metadata;
use PhpLlm\LlmChain\ToolBox\ToolBox;
use PhpLlm\LlmChain\ToolBox\ToolBoxInterface;

class SettingsToolBox implements ToolBoxInterface
{
    public function __construct(
        private readonly SettingsHandler $settingsHandler,
        private readonly ToolBox $llmToolBox,
    ) {
    }

    /** @return Metadata[] */
    public function getMap(): array
    {
        $toolMap = $this->llmToolBox->getMap();

        // Check Settings for custom tool names and descriptions and overhault the metadata here :)

        return $toolMap;
    }

    /**
     * There is no need for customization here as the purpose of this wrapper is to have the
     * possibility of changing the names and descriptions parsed from the class attributes if
     * there are custom settings of the application are available.
     */
    public function execute(ToolCall $toolCall): string
    {
        return $this->llmToolBox->execute($toolCall);
    }
}
