<?php

declare(strict_types=1);

namespace ChronicleKeeper\Settings\Domain\Entity;

use ChronicleKeeper\Settings\Domain\ValueObject\SystemPrompt\Purpose;
use ChronicleKeeper\Shared\Domain\Entity\AggregateRoot;
use JsonSerializable;
use LogicException;
use Symfony\Component\Uid\Uuid;

/**
 * @phpstan-type SystemPromptArray = array{
 *     id: string,
 *     purpose: string,
 *     name: string,
 *     content: string,
 *     isSystem: bool
 * }
 */
class SystemPrompt extends AggregateRoot implements JsonSerializable
{
    public function __construct(
        private readonly string $id,
        private readonly Purpose $purpose,
        private string $name,
        private string $content,
        private readonly bool $isSystem,
    ) {
    }

    public static function createSystemPrompt(string $id, Purpose $purpose, string $name, string $content): self
    {
        return new self($id, $purpose, $name, $content, true);
    }

    public static function create(Purpose $purpose, string $name, string $content): self
    {
        return new self(Uuid::v4()->toString(), $purpose, $name, $content, false);
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getPurpose(): Purpose
    {
        return $this->purpose;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function isSystem(): bool
    {
        return $this->isSystem;
    }

    public function rename(string $name): void
    {
        if ($this->isSystem) {
            throw new LogicException('System relevant prompts cannot be renamed.');
        }

        if ($name === $this->name) {
            return;
        }

        $this->name = $name;
    }

    public function changeContent(string $content): void
    {
        if ($content === $this->content) {
            return;
        }

        $this->content = $content;
    }

    /** @return SystemPromptArray */
    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id,
            'purpose' => $this->purpose->value,
            'name' => $this->name,
            'content' => $this->content,
            'isSystem' => $this->isSystem,
        ];
    }
}
