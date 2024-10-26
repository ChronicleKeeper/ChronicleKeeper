<?php

declare(strict_types=1);

namespace ChronicleKeeper\ImageGenerator\Domain\Entity;

use ChronicleKeeper\ImageGenerator\Domain\ValueObject\OptimizedPrompt;
use ChronicleKeeper\ImageGenerator\Domain\ValueObject\UserInput;
use JsonSerializable;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;
use Symfony\Component\Uid\Uuid;

#[Autoconfigure(autowire: false)]
class GeneratorRequest implements JsonSerializable
{
    public string $id;
    public OptimizedPrompt|null $prompt = null; // If null the optimization has not yet run!

    public function __construct(
        public string $title,
        public UserInput $userInput,
    ) {
        $this->id = Uuid::v4()->toString();
    }

    /**
     * @return array{
     *     id: string,
     *     prompt: string|null,
     *     title: string,
     *     userInput: string,
     * }
     */
    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id,
            'prompt' => $this->prompt?->prompt,
            'title' => $this->title,
            'userInput' => $this->userInput->prompt,
        ];
    }
}
