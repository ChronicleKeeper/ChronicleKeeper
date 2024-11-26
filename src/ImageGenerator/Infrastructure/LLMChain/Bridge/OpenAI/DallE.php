<?php

declare(strict_types=1);

namespace ChronicleKeeper\ImageGenerator\Infrastructure\LLMChain\Bridge\OpenAI;

use PhpLlm\LlmChain\Model\LanguageModel;

final class DallE implements LanguageModel
{
    public const string DALL_E_3 = 'dall-e-3';

    /** @param array<string, mixed> $options The default options for the model usage */
    public function __construct(
        private readonly string $version = self::DALL_E_3,
        private readonly array $options = ['response_format' => 'b64_json'],
    ) {
    }

    public function getVersion(): string
    {
        return $this->version;
    }

    /** @return array<string, mixed> */
    public function getOptions(): array
    {
        return $this->options;
    }

    public function supportsImageInput(): bool
    {
        return false;
    }

    public function supportsStructuredOutput(): bool
    {
        return false;
    }

    public function supportsStreaming(): bool
    {
        return false;
    }

    public function supportsToolCalling(): bool
    {
        return false;
    }
}
