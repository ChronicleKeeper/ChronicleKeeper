<?php

declare(strict_types=1);

namespace DZunke\NovDoc\Shared\Infrastructure\LLMChain;

class ToolUsageCollector
{
    /** @var list<array{tool: string, arguments: array<string,mixed>}> */
    private array $calls = [];

    /** @param array<string,mixed> $arguments */
    public function called(string $tool, array $arguments = []): void
    {
        $this->calls[] = ['tool' => $tool, 'arguments' => $arguments];
    }

    /** @return list<array{tool: string, arguments: array<string,mixed>}> */
    public function getCalls(): array
    {
        $calls       = $this->calls;
        $this->calls = [];

        return $calls;
    }
}
