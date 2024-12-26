<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\ImageGenerator\Domain\Entity;

use ChronicleKeeper\Image\Domain\Entity\Image;
use ChronicleKeeper\ImageGenerator\Domain\Entity\GeneratorResult;

class GeneratorResultBuilder
{
    private string $encodedImage;
    private string $revisedPrompt;
    private Image|null $image;
    private string $mimeType;

    public function __construct()
    {
        $this->encodedImage  = 'defaultEncodedImage';
        $this->revisedPrompt = 'I am the prompt that the server built from the user given prompt';
        $this->image         = null;
        $this->mimeType      = 'image/png';
    }

    public function withEncodedImage(string $encodedImage): self
    {
        $this->encodedImage = $encodedImage;

        return $this;
    }

    public function withRevisedPrompt(string $revisedPrompt): self
    {
        $this->revisedPrompt = $revisedPrompt;

        return $this;
    }

    public function withImage(Image|null $image): self
    {
        $this->image = $image;

        return $this;
    }

    public function withMimeType(string $mimeType): self
    {
        $this->mimeType = $mimeType;

        return $this;
    }

    public function build(): GeneratorResult
    {
        return new GeneratorResult($this->encodedImage, $this->revisedPrompt, $this->image, $this->mimeType);
    }
}
