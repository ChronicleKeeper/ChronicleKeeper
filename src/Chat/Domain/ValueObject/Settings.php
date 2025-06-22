<?php

declare(strict_types=1);

namespace ChronicleKeeper\Chat\Domain\ValueObject;

use JsonSerializable;
use PhpLlm\LlmChain\Platform\Bridge\OpenAI\GPT;

class Settings implements JsonSerializable
{
    public readonly string $version;

    public function __construct(
        string|null $version = null,
        public readonly float $temperature = 0.7,
        public readonly float $imagesMaxDistance = 0.7,
        public readonly float $documentsMaxDistance = 0.85,
    ) {
        $this->version = $version ?? GPT::GPT_4O_MINI;
    }

    public function equals(Settings $settings): bool
    {
        return $this->version === $settings->version
            && $this->temperature === $settings->temperature
            && $this->imagesMaxDistance === $settings->imagesMaxDistance
            && $this->documentsMaxDistance === $settings->documentsMaxDistance;
    }

    /**
     * @return array{
     *     version: string,
     *     temperature: float,
     *     images_max_distance: float,
     *     documents_max_distance: float,
     * }
     */
    public function jsonSerialize(): array
    {
        return [
            'version' => $this->version,
            'temperature' => $this->temperature,
            'images_max_distance' => $this->imagesMaxDistance,
            'documents_max_distance' => $this->documentsMaxDistance,
        ];
    }
}
