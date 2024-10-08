<?php

declare(strict_types=1);

namespace ChronicleKeeper\ImageGenerator\Application\Service\OpenAIGenerator;

class ResponseImage
{
    public function __construct(
        public string $prompt,
        public string $mimeType,
        public string $encodedImage,
    ) {
    }

    public function getImageUrl(): string
    {
        return 'data:' . $this->mimeType . ';base64,' . $this->encodedImage;
    }
}
