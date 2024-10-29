<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\ImageGenerator\Domain\Entity;

use ChronicleKeeper\ImageGenerator\Domain\Entity\GeneratorRequest;
use ChronicleKeeper\ImageGenerator\Domain\ValueObject\OptimizedPrompt;
use ChronicleKeeper\ImageGenerator\Domain\ValueObject\UserInput;

class GeneratorRequestBuilder
{
    private string $title;
    private UserInput $userInput;
    private OptimizedPrompt|null $optimizedPrompt = null;

    public function __construct()
    {
        $this->title     = 'Default Title';
        $this->userInput = new UserInput('Default Prompt');
    }

    public function withTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function withUserInput(UserInput $userInput): self
    {
        $this->userInput = $userInput;

        return $this;
    }

    public function withOptimizedPrompt(OptimizedPrompt|null $optimizedPrompt): self
    {
        $this->optimizedPrompt = $optimizedPrompt;

        return $this;
    }

    public function build(): GeneratorRequest
    {
        $generatorRequest         = new GeneratorRequest($this->title, $this->userInput);
        $generatorRequest->prompt = $this->optimizedPrompt;

        return $generatorRequest;
    }
}
