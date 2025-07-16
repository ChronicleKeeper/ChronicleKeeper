<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Chat\Domain\Entity;

use ChronicleKeeper\Chat\Domain\Entity\Message;
use ChronicleKeeper\Chat\Domain\Entity\MessageBag;

use function array_values;

class MessageBagBuilder
{
    /** @var list<Message> */
    private array $messages = [];

    public function withMessage(Message $message): self
    {
        $this->messages[] = $message;

        return $this;
    }

    public function withMessages(Message ...$messages): self
    {
        $this->messages = array_values([...$this->messages, ...$messages]);

        return $this;
    }

    public function build(): MessageBag
    {
        return new MessageBag(...$this->messages);
    }
}
