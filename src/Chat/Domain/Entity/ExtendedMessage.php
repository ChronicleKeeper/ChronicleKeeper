<?php

declare(strict_types=1);

namespace ChronicleKeeper\Chat\Domain\Entity;

use ChronicleKeeper\Chat\Domain\ValueObject\MessageContext;
use JsonSerializable;
use PhpLlm\LlmChain\Model\Message\MessageInterface;
use Symfony\Component\Uid\Uuid;

/**
 * @phpstan-type ExtendedMessageArray = array{
 *      id: string,
 *      message: MessageInterface,
 *      context: MessageContext,
 *  }
 */
class ExtendedMessage implements JsonSerializable
{
    public string $id;
    public MessageContext $context;

    public function __construct(
        public readonly MessageInterface $message,
        MessageContext|null $context = null,
    ) {
        $this->id      = Uuid::v4()->toString();
        $this->context = $context ?? new MessageContext();
    }

    /** @return ExtendedMessageArray */
    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id,
            'message' => $this->message,
            'context' => $this->context,
        ];
    }
}
