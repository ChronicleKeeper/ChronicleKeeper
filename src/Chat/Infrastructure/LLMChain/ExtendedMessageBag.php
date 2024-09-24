<?php

declare(strict_types=1);

namespace ChronicleKeeper\Chat\Infrastructure\LLMChain;

use ArrayObject;
use PhpLlm\LlmChain\Message\Message;
use PhpLlm\LlmChain\Message\MessageBag;

use function array_map;
use function array_values;

/** @template-extends ArrayObject<int, ExtendedMessage> */
final class ExtendedMessageBag extends ArrayObject
{
    public function __construct(ExtendedMessage ...$messages)
    {
        parent::__construct(array_values($messages));
    }

    public function getLLMChainMessages(): MessageBag
    {
        return new MessageBag(...array_map(
            static fn (ExtendedMessage $extendedMessage): Message => $extendedMessage->message,
            $this->getArrayCopy(),
        ));
    }
}
