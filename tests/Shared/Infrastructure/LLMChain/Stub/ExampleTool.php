<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Shared\Infrastructure\LLMChain\Stub;

use PhpLlm\LlmChain\Chain\ToolBox\Attribute\AsTool;

#[AsTool('ExampleTool', 'This is an example tool.')]
class ExampleTool
{
    /** @param string $foo This is an example description. */
    public function __invoke(string $foo): string
    {
        return $foo;
    }
}
