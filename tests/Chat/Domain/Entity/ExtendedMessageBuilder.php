<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Chat\Domain\Entity;

use ChronicleKeeper\Chat\Domain\Entity\ExtendedMessage;
use ChronicleKeeper\Chat\Domain\ValueObject\MessageContext;
use ChronicleKeeper\Test\Chat\Domain\Entity\LLMChain\SystemMessageBuilder;
use PhpLlm\LlmChain\Model\Message\MessageInterface;
use Symfony\Component\Uid\Uuid;

class ExtendedMessageBuilder
{
    private string $id;
    private MessageInterface $message;
    private MessageContext $context;

    public function __construct()
    {
        $this->id      = Uuid::v4()->toString();
        $this->message = (new SystemMessageBuilder())->build();
        $this->context = new MessageContext();
    }

    public function withId(string $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function withMessage(MessageInterface $message): self
    {
        $this->message = $message;

        return $this;
    }

    public function withContext(MessageContext $context): self
    {
        $this->context = $context;

        return $this;
    }

    public function build(): ExtendedMessage
    {
        $extendedMessage          = new ExtendedMessage($this->message);
        $extendedMessage->id      = $this->id;
        $extendedMessage->context = $this->context;

        return $extendedMessage;
    }
}
