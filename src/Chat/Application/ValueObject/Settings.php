<?php

declare(strict_types=1);

namespace ChronicleKeeper\Chat\Application\ValueObject;

use PhpLlm\LlmChain\OpenAI\Model\Gpt\Version;

class Settings
{
    public readonly string $version;

    public function __construct(
        string|null $version = null,
        public readonly float $temperature = 0.7,
        public readonly float $imagesMaxDistance = 0.7,
        public readonly float $documentsMaxDistance = 0.85,
    ) {
        $this->version = $version ?? Version::gpt4oMini()->name;
    }
}
