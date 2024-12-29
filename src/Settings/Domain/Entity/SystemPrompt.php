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
 *     isSystem: bool,
 *     isDefault: bool,
 * }
 */
class SystemPrompt extends AggregateRoot implements JsonSerializable
{
    public function __construct(
        private readonly string $id,
        private Purpose $purpose,
        private string $name,
        private string $content,
        private readonly bool $isSystem,
        private bool $isDefault,
    ) {
    }

    public static function createSystemPrompt(string $id, Purpose $purpose, string $name, string $content): self
    {
        return new self($id, $purpose, $name, $content, true, false);
    }

    public static function create(Purpose $purpose, string $name, string $content, bool $isDefault = false): self
    {
        return new self(Uuid::v4()->toString(), $purpose, $name, $content, false, $isDefault);
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

    public function isDefault(): bool
    {
        return $this->isDefault;
    }

    public function rename(string $name): void
    {
        if ($this->isSystem) {
            throw new LogicException('System relevant prompts cannot be changed.');
        }

        if ($name === $this->name) {
            return;
        }

        $this->name = $name;
    }

    public function changeContent(string $content): void
    {
        if ($this->isSystem) {
            throw new LogicException('System relevant prompts cannot be changed.');
        }

        if ($content === $this->content) {
            return;
        }

        $this->content = $content;
    }

    public function changePurpose(Purpose $purpose): void
    {
        if ($this->isSystem) {
            throw new LogicException('System relevant prompts cannot be changed.');
        }

        if ($this->purpose === $purpose) {
            return;
        }

        $this->purpose = $purpose;
    }

    public function toDefault(): void
    {
        if ($this->isSystem) {
            throw new LogicException('System relevant prompts cannot be set as default as they are already fallbacks when no default defined.');
        }

        if ($this->isDefault) {
            return;
        }

        $this->isDefault = true;
    }

    public function toNotDefault(): void
    {
        if ($this->isSystem) {
            throw new LogicException('System relevant prompts cannot be set as default as they are already fallbacks when no default defined.');
        }

        if (! $this->isDefault) {
            return;
        }

        $this->isDefault = false;
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
            'isDefault' => $this->isDefault,
        ];
    }
}
