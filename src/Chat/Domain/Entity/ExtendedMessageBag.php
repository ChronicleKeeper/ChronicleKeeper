<?php

declare(strict_types=1);

namespace ChronicleKeeper\Chat\Domain\Entity;

use ArrayObject;
use JsonSerializable;
use PhpLlm\LlmChain\Message\MessageBag;
use PhpLlm\LlmChain\Message\MessageInterface;

use function array_map;
use function array_values;

/** @template-extends ArrayObject<int, ExtendedMessage> */
final class ExtendedMessageBag extends ArrayObject implements JsonSerializable
{
    public function __construct(ExtendedMessage ...$messages)
    {
        parent::__construct(array_values($messages));
    }

    public function getLLMChainMessages(): MessageBag
    {
        return new MessageBag(...array_map(
            static fn (ExtendedMessage $extendedMessage): MessageInterface => $extendedMessage->message,
            $this->getArrayCopy(),
        ));
    }

    /** @return list<ExtendedMessage> */
    public function jsonSerialize(): array
    {
        return $this->getArrayCopy();
    }
}
