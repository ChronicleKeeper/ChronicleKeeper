<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Chat\Domain\Entity;

use ChronicleKeeper\Chat\Domain\Entity\Message;
use ChronicleKeeper\Chat\Domain\ValueObject\MessageContext;
use ChronicleKeeper\Chat\Domain\ValueObject\MessageDebug;
use ChronicleKeeper\Chat\Domain\ValueObject\Role;
use Symfony\Component\Uid\Uuid;

class MessageBuilder
{
    private string $id;
    private Role $role;
    private string $content;
    private MessageContext $context;
    private MessageDebug $debug;

    public function __construct()
    {
        $this->id      = Uuid::v4()->toString();
        $this->role    = Role::USER;
        $this->content = 'Default message content';
        $this->context = new MessageContext();
        $this->debug   = new MessageDebug();
    }

    public function withId(string $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function withRole(Role $role): self
    {
        $this->role = $role;

        return $this;
    }

    public function withContent(string $content): self
    {
        $this->content = $content;

        return $this;
    }

    public function withContext(MessageContext $context): self
    {
        $this->context = $context;

        return $this;
    }

    public function withDebug(MessageDebug $debug): self
    {
        $this->debug = $debug;

        return $this;
    }

    public function asSystem(): self
    {
        $this->role = Role::SYSTEM;

        return $this;
    }

    public function asUser(): self
    {
        $this->role = Role::USER;

        return $this;
    }

    public function asAssistant(): self
    {
        $this->role = Role::ASSISTANT;

        return $this;
    }

    public function build(): Message
    {
        return new Message(
            $this->id,
            $this->role,
            $this->content,
            $this->context,
            $this->debug,
        );
    }
}
