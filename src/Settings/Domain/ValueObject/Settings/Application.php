<?php

declare(strict_types=1);

namespace ChronicleKeeper\Settings\Domain\ValueObject\Settings;

/**
 * @phpstan-type ApplicationSettings = array{
 *     open_ai_api_key: string,
 * }
 */
readonly class Application
{
    public function __construct(
        public string $openAIApiKey = '',
    ) {
    }

    /** @param ApplicationSettings $settings */
    public static function fromArray(array $settings): Application
    {
        return new Application($settings['open_ai_api_key']);
    }

    /** @return ApplicationSettings */
    public function toArray(): array
    {
        return [
            'open_ai_api_key' => $this->openAIApiKey,
        ];
    }

    public function hasOpenAIApiKey(): bool
    {
        return $this->openAIApiKey !== '';
    }
}
