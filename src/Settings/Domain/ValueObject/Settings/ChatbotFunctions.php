<?php

declare(strict_types=1);

namespace ChronicleKeeper\Settings\Domain\ValueObject\Settings;

/**
 * @phpstan-type ChatbotFunctionsArray = array{
 *     allow_debug_output: bool,
 *     function_descriptions?: array<string, string>
 * }
 */
class ChatbotFunctions
{
    /** @param array<string, string> $functionDescriptions */
    public function __construct(
        private readonly bool $allowDebugOutput = false,
        private readonly array $functionDescriptions = [],
    ) {
    }

    /** @param ChatbotFunctionsArray $array */
    public static function fromArray(array $array): ChatbotFunctions
    {
        return new ChatbotFunctions(
            $array['allow_debug_output'],
            $array['function_descriptions'] ?? [],
        );
    }

    /** @return ChatbotFunctionsArray */
    public function toArray(): array
    {
        return [
            'allow_debug_output' => $this->allowDebugOutput,
            'function_descriptions' => $this->functionDescriptions,
        ];
    }

    public function isAllowDebugOutput(): bool
    {
        return $this->allowDebugOutput;
    }

    /** @return array<string, string> */
    public function getFunctionDescriptions(): array
    {
        return $this->functionDescriptions;
    }
}
