<?php

declare(strict_types=1);

namespace ChronicleKeeper\Chat\Domain\Entity;

use JsonSerializable;
use PhpLlm\LlmChain\Model\Message\MessageInterface;
use Symfony\Component\Uid\Uuid;

/**
 * @phpstan-type ExtendedMessageArray = array{
 *      id: string,
 *      message: MessageInterface,
 *  }
 */
class ExtendedMessage implements JsonSerializable
{
    public string $id;

    public function __construct(
        public readonly MessageInterface $message,
    ) {
        $this->id = Uuid::v4()->toString();
    }

    /** @return ExtendedMessageArray */
    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id,
            'message' => $this->message,
        ];
    }
}
