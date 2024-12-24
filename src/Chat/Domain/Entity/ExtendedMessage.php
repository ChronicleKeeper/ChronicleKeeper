<?php

declare(strict_types=1);

namespace ChronicleKeeper\Chat\Domain\Entity;

use ChronicleKeeper\Chat\Domain\ValueObject\MessageContext;
use ChronicleKeeper\Chat\Domain\ValueObject\MessageDebug;
use JsonSerializable;
use PhpLlm\LlmChain\Model\Message\MessageInterface;
use Symfony\Component\Uid\Uuid;

/**
 * @phpstan-type ExtendedMessageArray = array{
 *      id: string,
 *      message: MessageInterface,
 *      context: MessageContext,
 *      debug: MessageDebug,
 *  }
 */
class ExtendedMessage implements JsonSerializable
{
    public string $id;
    public MessageContext $context;
    public MessageDebug $debug;

    public function __construct(
        public readonly MessageInterface $message,
        MessageContext|null $context = null,
        MessageDebug|null $debug = null,
    ) {
        $this->id      = Uuid::v4()->toString();
        $this->context = $context ?? new MessageContext();
        $this->debug   = $debug ?? new MessageDebug();
    }

    /** @return ExtendedMessageArray */
    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id,
            'message' => $this->message,
            'context' => $this->context,
            'debug' => $this->debug,
        ];
    }
}
