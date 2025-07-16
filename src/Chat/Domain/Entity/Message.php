<?php

declare(strict_types=1);

namespace ChronicleKeeper\Chat\Domain\Entity;

use ChronicleKeeper\Chat\Domain\ValueObject\MessageContext;
use ChronicleKeeper\Chat\Domain\ValueObject\MessageDebug;
use ChronicleKeeper\Chat\Domain\ValueObject\Role;
use Symfony\Component\Uid\Uuid;

class Message
{
    public function __construct(
        private readonly string $id,
        private readonly Role $role,
        private readonly string $content,
        private readonly MessageContext $context = new MessageContext(),
        private readonly MessageDebug $debug = new MessageDebug(),
    ) {
    }

    public static function forSystem(string $content): self
    {
        return new self(
            id: Uuid::v4()->toString(),
            role: Role::SYSTEM,
            content: $content,
        );
    }

    public static function forUser(string $content): self
    {
        return new self(
            id: Uuid::v4()->toString(),
            role: Role::USER,
            content: $content,
        );
    }

    public static function forAssistant(string $content, MessageContext $context, MessageDebug $debug): self
    {
        return new self(
            id: Uuid::v4()->toString(),
            role: Role::ASSISTANT,
            content: $content,
            context: $context,
            debug: $debug,
        );
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function getRole(): Role
    {
        return $this->role;
    }

    public function isRole(Role $role): bool
    {
        return $this->role === $role;
    }

    public function isSystem(): bool
    {
        return $this->role === Role::SYSTEM;
    }

    public function isUser(): bool
    {
        return $this->role === Role::USER;
    }

    public function isAssistant(): bool
    {
        return $this->role === Role::ASSISTANT;
    }

    public function getContext(): MessageContext
    {
        return $this->context;
    }

    public function getDebug(): MessageDebug
    {
        return $this->debug;
    }
}
