<?php

declare(strict_types=1);

namespace ChronicleKeeper\Chat\Domain\ValueObject;

use PhpLlm\LlmChain\Bridge\OpenAI\GPT;

class Settings
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
}
