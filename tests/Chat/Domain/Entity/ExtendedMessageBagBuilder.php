<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Chat\Domain\Entity;

use ChronicleKeeper\Chat\Domain\Entity\ExtendedMessage;
use ChronicleKeeper\Chat\Domain\Entity\ExtendedMessageBag;

use function array_values;

class ExtendedMessageBagBuilder
{
    /** @var list<ExtendedMessage> */
    private array $messages = [];

    public function __construct()
    {
        $this->messages[] = (new ExtendedMessageBuilder())->build();
    }

    public function withMessages(ExtendedMessage ...$messages): self
    {
        $this->messages = array_values($messages);

        return $this;
    }

    public function build(): ExtendedMessageBag
    {
        return new ExtendedMessageBag(...$this->messages);
    }
}
