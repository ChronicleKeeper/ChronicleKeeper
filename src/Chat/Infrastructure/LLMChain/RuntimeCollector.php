<?php

declare(strict_types=1);

namespace ChronicleKeeper\Chat\Infrastructure\LLMChain;

use ChronicleKeeper\Chat\Domain\ValueObject\FunctionDebug;
use ChronicleKeeper\Chat\Domain\ValueObject\Reference;
use ChronicleKeeper\Settings\Application\SettingsHandler;

use function array_filter;
use function array_values;
use function usort;

class RuntimeCollector
{
    public function __construct(
        private readonly SettingsHandler $settingsHandler,
    ) {
    }

    /** @var list<Reference> */
    private array $references = [];
    /** @var list<FunctionDebug> */
    private array $functionDebug = [];

    public function addReference(Reference $reference): void
    {
        $this->references[] = $reference;

        // Sort the references by title
        usort($this->references, static fn (Reference $a, Reference $b) => $a->title <=> $b->title);
    }

    public function addFunctionDebug(FunctionDebug $functionDebug): void
    {
        if (! $this->settingsHandler->get()->getChatbotFunctions()->isAllowDebugOutput()) {
            // Only store the debug information when the debug is enabled for storage optimization
            return;
        }

        $this->functionDebug[] = $functionDebug;
    }

    /** @return list<Reference> */
    public function flushReferenceByType(string $type): array
    {
        $references = array_filter(
            $this->references,
            static fn (Reference $reference) => $reference->type === $type,
        );

        if ($references === []) {
            return [];
        }

        $this->references = array_values(array_filter(
            $this->references,
            static fn (Reference $reference) => $reference->type !== $type,
        ));

        return array_values($references);
    }

    /** @return list<FunctionDebug> */
    public function flushFunctionDebug(): array
    {
        $functions           = $this->functionDebug;
        $this->functionDebug = [];

        return $functions;
    }

    /** @return list<FunctionDebug> */
    public function flushFunctionDebugByTool(string $tool): array
    {
        $debug = array_filter(
            $this->functionDebug,
            static fn (FunctionDebug $functionDebug) => $functionDebug->tool === $tool,
        );

        if ($debug === []) {
            return [];
        }

        $this->functionDebug = array_values(array_filter(
            $this->functionDebug,
            static fn (FunctionDebug $functionDebug) => $functionDebug->tool !== $tool,
        ));

        return array_values($debug);
    }

    public function reset(): void
    {
        $this->references    = [];
        $this->functionDebug = [];
    }
}
