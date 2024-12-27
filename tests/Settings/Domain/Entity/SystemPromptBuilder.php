<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Settings\Domain\Entity;

use ChronicleKeeper\Settings\Domain\Entity\SystemPrompt;
use ChronicleKeeper\Settings\Domain\ValueObject\SystemPrompt\Purpose;
use Symfony\Component\Uid\Uuid;

class SystemPromptBuilder
{
    private string $id;
    private Purpose $purpose = Purpose::CONVERSATION;
    private string $name     = 'I am a prompt';
    private string $content  = 'You will act as a tiny bot that is funny with the user and answers everything with a joke.';
    private bool $isSystem   = true;

    public function __construct()
    {
        $this->id = Uuid::v4()->toString();
    }

    public function withId(string $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function withPurpose(Purpose $purpose): self
    {
        $this->purpose = $purpose;

        return $this;
    }

    public function withName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function withContent(string $content): self
    {
        $this->content = $content;

        return $this;
    }

    public function asSystem(): self
    {
        $this->isSystem = true;

        return $this;
    }

    public function asUser(): self
    {
        $this->isSystem = false;

        return $this;
    }

    public function build(): SystemPrompt
    {
        return new SystemPrompt(
            $this->id,
            $this->purpose,
            $this->name,
            $this->content,
            $this->isSystem,
        );
    }
}
