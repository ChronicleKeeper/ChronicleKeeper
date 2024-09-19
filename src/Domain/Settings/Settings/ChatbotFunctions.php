<?php

declare(strict_types=1);

namespace DZunke\NovDoc\Domain\Settings\Settings;

/**
 * @phpstan-type ChatbotFunctionsArray = array{
 *     allow_debug_output: bool
 * }
 */
class ChatbotFunctions
{
    public function __construct(
        private readonly bool $allowDebugOutput = false,
    ) {
    }

    /** @param ChatbotFunctionsArray $array */
    public static function fromArray(array $array): ChatbotFunctions
    {
        return new ChatbotFunctions(
            $array['allow_debug_output'],
        );
    }

    /** @return ChatbotFunctionsArray */
    public function toArray(): array
    {
        return [
            'allow_debug_output' => $this->allowDebugOutput,
        ];
    }

    public function isAllowDebugOutput(): bool
    {
        return $this->allowDebugOutput;
    }
}
