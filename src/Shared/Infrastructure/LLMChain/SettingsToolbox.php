<?php

declare(strict_types=1);

namespace ChronicleKeeper\Shared\Infrastructure\LLMChain;

use ChronicleKeeper\Settings\Application\SettingsHandler;
use PhpLlm\LlmChain\Chain\Toolbox\Metadata;
use PhpLlm\LlmChain\Chain\Toolbox\ToolboxInterface;
use PhpLlm\LlmChain\Model\Response\ToolCall;

use function array_key_exists;

class SettingsToolbox implements ToolboxInterface
{
    public function __construct(
        private readonly SettingsHandler $settingsHandler,
        private readonly ToolboxInterface $llmToolBox,
    ) {
    }

    /** @return Metadata[] */
    public function getMap(): array
    {
        $descriptions = $this->settingsHandler->get()->getChatbotFunctions()->getFunctionDescriptions();
        $toolMap      = $this->llmToolBox->getMap();
        // Check Settings for custom tool names and descriptions and overhault the metadata here :)

        foreach ($toolMap as $index => $tool) {
            if (! array_key_exists($tool->name, $descriptions)) {
                continue;
            }

            if ($descriptions[$tool->name] === '') {
                continue;
            }

            $toolMap[$index] = new Metadata(
                $tool->reference,
                $tool->name,
                $descriptions[$tool->name],
                $tool->parameters,
            );
        }

        return $toolMap;
    }

    /**
     * There is no need for customization here as the purpose of this wrapper is to have the
     * possibility of changing the names and descriptions parsed from the class attributes if
     * there are custom settings of the application are available.
     */
    public function execute(ToolCall $toolCall): mixed
    {
        return $this->llmToolBox->execute($toolCall);
    }
}
